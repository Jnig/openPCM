<?php

namespace Jan\ZevirtBundle\Service;

use Jan\ZevirtBundle\Entity\VirtualMachine;
use Jan\ZevirtBundle\Service\NodeService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\Node;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class VirtualmachineService {

    private $em;
    protected $container;
    private $nodeService;

    public function __construct(ContainerInterface $container, $em, NodeService $nodeService) {
        $this->em = $em;
        $this->nodeService = $nodeService;
        $this->container = $container;
    }

    public function isRunning(VirtualMachine $vm) {
        $connection = $this->nodeService->getConnection($vm->getNode());

        try {
            $out = $connection->exec('virsh dominfo ' . $vm->getRealName());
        } catch (\Exception $e) {
            $out = '';
        }
        if (strpos($out, 'running') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function getVncPort(VirtualMachine $vm) {
        $connection = $this->nodeService->getConnection($vm->getNode());

        if ($this->isRunning($vm)) {
            $output = $connection->exec('virsh vncdisplay ' . $vm->getRealName());

            $lines = explode("\n", $output);
            $port = explode(':', $lines[0]);

            return 5900 + $port[1];
        } else {
            return false;
        }
    }

    public function pacemakerAdd(VirtualMachine $vm) {
        $hostname = $this->nodeService->getShortHostname($vm->getNodeDefault());

        $resourceName = $vm->getRealName();

        if ($this->isRunning($vm)) {
            $role = 'Started';
        } else {
            $role = 'Stopped';
        }

        $text = '

 <configuration>
 <resources>
 <primitive class="ocf" id="' . $resourceName . '" provider="heartbeat" type="VirtualDomain">
      <instance_attributes id="' . $resourceName . '-instance_attributes">
        <nvpair id="' . $resourceName . '-instance_attributes-config" name="config" value="/etc/libvirt/qemu/' . $resourceName . '.xml"/>
        <nvpair id="' . $resourceName . '-instance_attributes-hypervisor" name="hypervisor" value="qemu:///system"/>
        <nvpair id="' . $resourceName . '-instance_attributes-migration_transport" name="migration_transport" value="ssh"/>
      </instance_attributes>
      <meta_attributes id="' . $resourceName . '-meta_attributes">
        <nvpair id="' . $resourceName . '-meta_attributes-allow-migrate" name="allow-migrate" value="true"/>
        <nvpair id="' . $resourceName . '-meta_attributes-target-role" name="target-role" value="' . $role . '"/>
      </meta_attributes>
      <operations>
        <op id="' . $resourceName . '-start-0" interval="0" name="start" timeout="30"/>
        <op id="' . $resourceName . '-stop-0" interval="0" name="stop" timeout="55"/>
        <op id="' . $resourceName . '-monitor-10" interval="10" name="monitor" timeout="30">
          <instance_attributes id="' . $resourceName . '-monitor-10-instance_attributes">
            <nvpair id="' . $resourceName . '-monitor-10-instance_attributes-depth" name="depth" value="0"/>
          </instance_attributes>
        </op>
        <op id="' . $resourceName . '-migrate_from-0" interval="0" name="migrate_from" timeout="300"/>
        <op id="' . $resourceName . '-migrate_to-0" interval="0" name="migrate_to" timeout="300"/>
      </operations>
    </primitive>
</resources>
    <constraints>
      <rsc_location id="l-' . $resourceName . '-on-' . $vm->getNodeDefault()->getRealName() . '" node="' . $hostname . '" rsc="' . $resourceName . '" score="INFINITY"/>
    </constraints>
</configuration>

';

        $this->container->get('cluster.service')->cibadminAdd($vm->getNode()->getCluster(), $text);
    }

    public function pacemakerDelete(VirtualMachine $vm) {

        $resourceName = $vm->getRealName();

        // first delete constraint then vm, otherwise cibadmin fails
        $this->container->get('cluster.service')->cibadminDelete($vm->getNode()->getCluster(), '<rsc_location id="l-' . $resourceName . '-on-' . $vm->getNode()->getRealName() . '" />');
        $this->container->get('cluster.service')->cibadminDelete($vm->getNode()->getCluster(), '<primitive class="ocf" id="' . $resourceName . '" />');
    }

    public function persist(VirtualMachine $vm) {
        $this->em->persist($vm);
        $this->em->flush($vm);

        if ($this->container->get('control.service')->isCli()) {
            $this->persistTasks($vm);
        } else {
            $this->container->get('job.service')->virtualmachinePersist($vm);
        }
    }

    public function remove(VirtualMachine $vm) {
        if ($this->container->get('control.service')->isCli()) {
            $this->removeTasks($vm);
        } else {
            $this->container->get('job.service')->virtualmachineRemove($vm);
        }
    }

    public function removeTasks(VirtualMachine $vm) {
        $out = $this->container->get('control.service')->getOutputWriter();

        $out->writeln("removing disks");
        foreach ($vm->getDisks() as $disk) {
            $this->container->get('disk.service')->remove($disk);
        }


        $out->writeln("Undefining {$vm->getName()} Libvirt definitions from cluster");
        if ($this->isRunning($vm)) {
            throw new \Exception('Virtualmachine ' . $vm->getName() . ' is running. Stop it first');
        } else {
            if ($vm->isHa()) {
                $out->writeln("HA detected");
                foreach ($vm->getNode()->getCluster()->getNodes() as $node) {
                    $this->nodeService->undefineVirtualmachine($node, $vm);
                }
            } else {
                $this->nodeService->undefineVirtualmachine($vm->getNode(), $vm);
            }
        }

        $this->em->remove($vm);
        $this->em->flush();
    }

    public function persistTasks(VirtualMachine $vm) {
        $out = $this->container->get('control.service');
        $out->writeln("Saveing {$vm->getName()} Libvirt definitions to cluster");
        if ($vm->isHa()) {
            $out->writeln("HA detected");
            foreach ($vm->getNode()->getCluster()->getNodes() as $node) {
                $this->nodeService->defineVirtualmachine($node, $vm);
            }

            $this->pacemakerAdd($vm);
        } else {
            $this->nodeService->defineVirtualmachine($vm->getNode(), $vm);
        }
    }

    public function toXml(Virtualmachine $vm) {
        $hypervisor = $this->container->getParameter('hypervisor');

        $disks = $vm->getDisks();
        $interfaces = $vm->getNetworkInterfaces();

        $disksXml = '';
        $interfacesXml = '';

        $alpha = range('a', 'z');
        $i = 0;
        foreach ($disks as $disk) {

            if (!$disk->getChild()) {
                $disk->setTargetDev('hd' . $alpha[$i]);
                $i++;

                $disksXml .= $disk->toXml() . "\n";
            }
        }

        foreach ($interfaces as $interface) {
            $interfacesXml .= $interface->toXml() . "\n";
        }



        $ret = '<domain type="' . $hypervisor . '">
  <uuid>' . $vm->getUuid() . '</uuid>
  <name>vm_' . $vm->getId() . '</name>
  <vcpu>' . $vm->getVcpu() . '</vcpu>
  <memory>' . ($vm->getMemory() * 1024) . '</memory>
  <clock offset="utc"/>
  <os machine="pc-0.14">
    <type>hvm</type>
    <boot dev="' . $vm->getBootDev() . '"/>
    <bootmenu enable="yes"/>
  </os>
  <features>
    <acpi/>
    <apic/>
  </features>
  <devices>
    <graphics type="vnc" autoport="yes" keymap="de"/>
    <input type="tablet" />
    ' . $disksXml . '
    ' . $interfacesXml . '        
  </devices>
</domain>';

        return $ret;
    }

    public function actionDestroy(VirtualMachine $vm) {
        $name = $vm->getRealName();
        $connection = $this->container->get('node.service')->getConnection($vm->getNode());
        if ($vm->isHa()) {
            $connection->exec("crm_resource --resource $name --set-parameter force_stop --parameter-value 1");
            $connection->exec("crm_resource --resource $name --set-parameter target-role --meta --parameter-value Stopped");
            $connection->exec("crm_resource --resource $name --set-parameter force_stop --parameter-value 0");
        } else {
            $connection->exec("virsh destroy $name");
            ;
        }
    }

    public function actionStart(VirtualMachine $vm) {


        $name = $vm->getRealName();
        $connection = $this->container->get('node.service')->getConnection($vm->getNode());
        if ($vm->isHa()) {
            if ($this->container->get('cluster.service')->verfiyClusterConfig($vm->getNode())) {
                $connection->exec("crm_resource --resource $name --set-parameter target-role --meta --parameter-value Started");
            } else {
                exit(1);
            }
        } else {
            $connection->exec("virsh start $name");
            ;
        }
    }

    public function actionShutdown(VirtualMachine $vm) {

        $name = $vm->getRealName();
        $connection = $this->container->get('node.service')->getConnection($vm->getNode());
        if ($vm->isHa()) {
            $connection->exec("crm_resource --resource $name --set-parameter target-role --meta --parameter-value Stopped");
        } else {
            $connection->exec("virsh shutdown $name");
            ;
        }
    }

    public function actionMigrate(VirtualMachine $vm, Node $node) {
        $out = $this->container->get('control.service');

        $hostname = $this->nodeService->getShortHostname($node);
        $resourceName = $vm->getRealName();
        if ($vm->isHa()) {
            $text = '
        <configuration>
                <constraints>
                    <rsc_location id="l-' . $resourceName . '-on-' . $node->getRealName() . '" node="' . $hostname . '" rsc="' . $resourceName . '" score="INFINITY"/>
                </constraints>
        </configuration>';
            $this->container->get('node.service')->cibadminAdd($node, $text);

            $this->container->get('node.service')->cibadminDelete($node, '<rsc_location id="l-' . $resourceName . '-on-' . $vm->getNode()->getRealName() . '"/>');
        } else {
            $this->getNode()->getConnection()->exec('virsh migrate --live ' . $vm->getRealName() . ' qemu+ssh://' . $node->getHostname() . '/system');
        }

        $vm->setNode($node);

        if ($this->isRunningWait($vm, 180)) {
            $out->writeln("{$vm->getName()} successful migrated");
        } else {
            $out->writeln("{$vm->getName()} migration failed");
        }

        $this->em->persist($vm);
        $this->em->flush();
    }

    public function isRunningWait($vm, $seconds = 60) {
        $out = $this->container->get('control.service')->getOutputWriter();
        $out->writeln("Waiting up to $seconds seconds for virtual machine");
        for ($i = 0; $i <= ($seconds); $i++) {
            if (!$this->isRunning($vm)) {
                $out->write('.');
                sleep(1);
            } else {
                $out->writeln('');
                return 1;
            }
        }

        return 0;
    }

}

?>
