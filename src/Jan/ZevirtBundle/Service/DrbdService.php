<?php

namespace Jan\ZevirtBundle\Service;

use Jan\ZevirtBundle\Entity\DiskLogical;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\DiskDrbd;
use Jan\ZevirtBundle\Entity\Node;

class DrbdService {

    private $em;
    protected $container;

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    public function postPersist(DiskDrbd $disk) {
        $vm = $disk->getVirtualmachine();

        $cluster = $vm[0]->getNode()->getCluster();
        $count = $this->getNextFreeDrbdCountByCluster($cluster);
        $disk->setCounter($count);
        $disk->setPath($disk->getDevice());


        $this->em->persist($disk);
        $this->em->flush();

        //jumps to preUpdate because of counter flush
    }

    public function preUpdate(DiskDrbd $disk) {

        //$node = $disk->getDiskA()->getNode();
        //$this->saveConfig($disk);        
        //$this->setup($disk);
        //if ($node->isHa()) {            
        //$this->container->get('node.service')->crmConfigure($node, $this->toCrm($disk));
        // } 
    }

    public function preRemove(DiskDrbd $disk) {
        
    }

    public function pacemakerAdd(\Jan\ZevirtBundle\Entity\DiskDrbd $disk, $state = 'Started') {
        $vm = $disk->getVirtualmachine();

        $name = $disk->getRealName();
        $vmName = $vm[0]->getRealName();

        $nodeA = $disk->getNodeA();
        $nodeB = $disk->getNodeB();

        $hostnameA = $this->container->get('node.service')->getShortHostname($disk->getDiskA()->getNode());
        $hostnameB = $this->container->get('node.service')->getShortHostname($disk->getDiskB()->getNode());

        $content = '

<configuration>
    <resources>
        <master id="ms_' . $name . '">
                <meta_attributes id="ms_' . $name . '-meta_attributes">
                  <nvpair id="ms_' . $name . '-meta_attributes-master-max" name="master-max" value="2"/>
                  <nvpair id="ms_' . $name . '-meta_attributes-clone-max" name="clone-max" value="2"/>
                  <nvpair id="ms_' . $name . '-meta_attributes-notify" name="notify" value="true"/>
                  <nvpair id="ms_' . $name . '-meta_attributes-target-role" name="target-role" value="' . $state . '"/>
                </meta_attributes>
                <primitive class="ocf" id="' . $name . '" provider="linbit" type="drbd">
                  <instance_attributes id="' . $name . '-instance_attributes">
                    <nvpair id="' . $name . '-instance_attributes-drbd_resource" name="drbd_resource" value="r' . $disk->getCounter()->getValue() . '"/>
                  </instance_attributes>
                  <operations>
                    <op id="' . $name . '-monitor-5s" interval="5s" name="monitor"/>
                  </operations>
                </primitive>
        </master>
    </resources>
    <constraints>
      <rsc_order first="ms_' . $name . '" first-action="promote" id="order-' . $name . '" score="INFINITY" then="' . $vmName . '" then-action="start"/>
      <rsc_colocation id="l-' . $vmName . '-on-ms_' . $name . '" rsc="' . $vmName . '" score="INFINITY" with-rsc="ms_' . $name . '" with-rsc-role="Master"/>
      

      
    </constraints>
</configuration>
';

        $this->container->get('node.service')->cibadminAdd($disk->getNodeA(), $content);
    }

    public function pacemakerDelete(\Jan\ZevirtBundle\Entity\DiskDrbd $disk) {
        $name = $disk->getRealName();
        $vm = $disk->getVirtualmachine();
        $vmName = $vm[0]->getRealName();

        // first delete constraint then vm, otherwise cibadmin fails
        $this->container->get('node.service')->cibadminDelete($disk->getNodeA(), '<rsc_order id="order-' . $name . '" />');
        $this->container->get('node.service')->cibadminDelete($disk->getNodeA(), '<rsc_colocation id="l-' . $vmName . '-on-ms_' . $name . '" />');
        $this->container->get('node.service')->cibadminDelete($disk->getNodeA(), '<master id="ms_' . $name . '">');
    }

    private function getConfig(DiskDrbd $disk) {
        if (!$disk->getId()) {
            throw new \Exception('Persist drbd entity before saving configuration');
        }

        $hostnameA = $this->container->get('node.service')->getShortHostname($disk->getDiskA()->getNode());
        $hostnameB = $this->container->get('node.service')->getShortHostname($disk->getDiskB()->getNode());

        $port = $disk->getPort();
        $ret = "
resource {$disk->getResourceName()} {
    net { 
            allow-two-primaries; 
            after-sb-0pri discard-zero-changes;
            after-sb-1pri discard-secondary;
            after-sb-2pri disconnect;
    }
    startup { become-primary-on both; }
  on {$hostnameA} {
    device    {$disk->getDevice()};
    disk      {$disk->getDiskA()->getPath()};
    address   {$disk->getIpA()}:$port;
    meta-disk internal;
  }
  on {$hostnameB} {
    device    {$disk->getDevice()};
    disk      {$disk->getDiskB()->getPath()};
    address   {$disk->getIpB()}:$port;
    meta-disk internal;
  }
}    
";
        return $ret;
    }

    public function getGlobalConfig() {
        $ret = '
global {
	usage-count yes;
	minor-count 255;
	# minor-count dialog-refresh disable-ip-verification
}

common {
	protocol C;

	handlers {
		pri-on-incon-degr "/usr/lib/drbd/notify-pri-on-incon-degr.sh; /usr/lib/drbd/notify-emergency-reboot.sh; echo b > /proc/sysrq-trigger ; reboot -f";
		pri-lost-after-sb "/usr/lib/drbd/notify-pri-lost-after-sb.sh; /usr/lib/drbd/notify-emergency-reboot.sh; echo b > /proc/sysrq-trigger ; reboot -f";
		local-io-error "/usr/lib/drbd/notify-io-error.sh; /usr/lib/drbd/notify-emergency-shutdown.sh; echo o > /proc/sysrq-trigger ; halt -f";
		fence-peer "/usr/lib/drbd/crm-fence-peer.sh";
		# split-brain "/usr/lib/drbd/notify-split-brain.sh root";
		# out-of-sync "/usr/lib/drbd/notify-out-of-sync.sh root";
		# before-resync-target "/usr/lib/drbd/snapshot-resync-target-lvm.sh -p 15 -- -c 16k";
		# after-resync-target /usr/lib/drbd/unsnapshot-resync-target-lvm.sh;
	}

	startup {
		become-primary-on both;
		# wfc-timeout degr-wfc-timeout outdated-wfc-timeout wait-after-sb
	}


	disk {
		fencing resource-and-stonith;
		# on-io-error fencing use-bmbv no-disk-barrier no-disk-flushes
		# no-disk-drain no-md-flushes max-bio-bvecs
	}

	net {
		allow-two-primaries;
		# sndbuf-size rcvbuf-size timeout connect-int ping-int ping-timeout max-buffers
		# max-epoch-size ko-count allow-two-primaries cram-hmac-alg shared-secret
		# after-sb-0pri after-sb-1pri after-sb-2pri data-integrity-alg no-tcp-cork
	}

	syncer {
		rate 100M;
		# rate after al-extents use-rle cpu-mask verify-alg csums-alg
	}


}
  
';
        return $ret;
    }

    public function deleteConfig(DiskDrbd $disk) {
        if ($disk->getDiskA()) {
            $disk->getDiskA()->setChild(0);
            $node = $disk->getDiskA()->getNode();
            try {
                $this->container->get('node.service')->getConnection($node)->exec('rm /etc/drbd.d/' . $disk->getResourceName() . '.res');
            } catch (\Exception $e) {
                
            }
        }

        if ($disk->getDiskB()) {
            $disk->getDiskB()->setChild(0);
            $node = $disk->getDiskB()->getNode();
            try {
                $this->container->get('node.service')->getConnection($node)->exec('rm /etc/drbd.d/' . $disk->getResourceName() . '.res');
            } catch (\Exception $e) {
                
            }
        }
    }

    public function saveConfig(DiskDrbd $disk) {
        $this->saveToNode($disk->getDiskA()->getNode(), $disk);
        $this->saveToNode($disk->getDiskB()->getNode(), $disk);
    }

    public function saveToNode(Node $node, DiskDrbd $disk) {
        try {
            $this->container->get('node.service')->getConnection($node)->exec('mkdir /etc/drbd.d/');
        } catch (\Exception $e) {
            
        }

        $this->container->get('node.service')->getConnection($node)->filePutContents('/etc/drbd.d/' . $disk->getResourceName() . '.res', $this->getConfig($disk));
        $this->container->get('node.service')->getConnection($node)->filePutContents('/etc/drbd.d/global_common.conf', $this->getGlobalConfig());
    }

    public function setup(DiskDrbd $disk) {
        
    }

    public function getNextFreeDrbdCountByCluster($cluster) {
        $entities = $this->em->getRepository('JanZevirtBundle:CounterDrbd')->findBy(array('cluster' => $cluster), array('value' => 'ASC'));

        $i = 0;
        foreach ($entities as $entity) {
            if ($entity->getValue() != $i) {
                break;
            } else {
                $i++;
            }
        }

        $drbdCount = new \Jan\ZevirtBundle\Entity\CounterDrbd;
        $drbdCount->setValue($i);
        $drbdCount->setCluster($cluster);

        $this->em->persist($drbdCount);
        $this->em->flush();

        return $drbdCount;
    }

    public function isConnected(DiskDrbd $disk) {

        $ret = $this->container->get('node.service')->getConnection($disk->getDiskA()->getNode())->exec('drbdadm cstate ' . $disk->getResourceName() . ' 2> /dev/null');
        if (strpos($ret, 'Connected') !== false) {
            return true;
        }
    }

    public function persist(DiskDrbd $disk) {
        $this->em->persist($disk);
        $this->em->flush($disk);

        if ($this->container->get('control.service')->isCli()) {
            $this->persistTasks($disk);
        } else {
            $this->container->get('job.service')->drbdPersist($disk);
        }
    }

    public function remove(DiskDrbd $disk) {
        if ($this->container->get('control.service')->isCli()) {
            $this->removeTasks($disk);
        } else {
            $this->container->get('job.service')->drbdRemove($disk);
        }
    }

    public function removeTasks(DiskDrbd $disk) {
        $out = $this->container->get('control.service')->getOutputWriter();

        $this->pacemakerAdd($disk, 'Stopped');


        $out->writeln('Waiting up to 1 minute for drbd to disconnect');
        for ($i = 0; $i <= (1 * 60); $i++) {
            if ($this->isConnected($disk)) {
                $out->write('.');
            } else {
                $out->writeln('');
                $out->writeln('Disconnect successfull');
                $this->pacemakerDelete($disk);

                $this->deleteConfig($disk);

                $this->em->remove($disk);
                $this->em->flush();

                $vms = $disk->getVirtualmachine();

                $this->container->get('virtualmachine.service')->persistTasks($vms[0]);

                $this->container->get('disk.service')->removeTasks($disk->getDiskA());
                $this->container->get('disk.service')->removeTasks($disk->getDiskB());

                return 0;
            }
            sleep(1);
        }
    }

    public function persistTasks(DiskDrbd $disk) {
        $out = $this->container->get('control.service');

        if (!count($disk->getDiskA()) || !count($disk->getDiskB())) {
            $this->allocateDisks($disk);
        }
        $this->saveConfig($disk);

        try {
            $this->container->get('node.service')->getConnection($disk->getDiskA()->getNode())->exec('echo "yes" | drbdadm create-md ' . $disk->getResourceName());
        } catch (\Exception $e) {
            
        }
        try {
            $this->container->get('node.service')->getConnection($disk->getDiskB()->getNode())->exec('echo "yes" | drbdadm create-md ' . $disk->getResourceName());
        } catch (\Exception $e) {
            
        }


        $this->pacemakerAdd($disk);

        $out->writeln('Waiting up to 1 minute for drbd to connect');
        for ($i = 0; $i <= (1 * 60); $i++) {
            if (!$this->isConnected($disk)) {
                $out->write('.');
            } else {
                try {
                    $this->container->get('node.service')->getConnection($disk->getDiskA()->getNode())->exec('drbdadm -- --overwrite-data-of-peer primary ' . $disk->getResourceName());
                } catch (\Exception $e) {
                    
                }


                $this->em->persist($disk);
                $this->em->flush();
                $out->writeln('.');

                return 0;
            }
            sleep(1);
        }
    }

    public function allocateDisks(\Jan\ZevirtBundle\Entity\DiskDrbd $drbdDisk) {
        $out = $this->container->get('control.service')->getOutputWriter();
        if ($drbdDisk->getNodeB()->getId() == $drbdDisk->getNodeA()->getId()) {
            throw new \Exception('Disk A and B must be on different nodes');
        }

        if (!count($drbdDisk->getDiskA())) {
            $out->writeln('Allocating DiskA');

            $diskA = new DiskLogical();
            $diskA->setNode($drbdDisk->getNodeA());
            $diskA->setStorage($drbdDisk->getStorage());
            $diskA->setCapacity((int) $drbdDisk->getCapacity());
            $diskA->setCreated(false);

            $diskA->setChild(1);
            $diskA->setDiskDevice($drbdDisk->getDiskDevice());
            $diskA->setDriverType($drbdDisk->getDriverType());

            $this->em->persist($diskA);
            $this->em->flush();
            $drbdDisk->setDiskA($diskA);
            $this->container->get('disk.service')->persist($diskA);
        }

        if (!count($drbdDisk->getDiskB())) {
            $out->writeln('Allocating DiskB');
            $diskB = new DiskLogical();
            $diskB->setNode($drbdDisk->getNodeB());
            $diskB->setStorage($drbdDisk->getStorage());
            $diskB->setCapacity((int) $drbdDisk->getCapacity());
            $diskB->setCreated(false);
            $diskB->setChild(1);
            $diskB->setDiskDevice($drbdDisk->getDiskDevice());
            $diskB->setDriverType($drbdDisk->getDriverType());

            $this->em->persist($diskB);
            $this->em->flush();
            $drbdDisk->setDiskB($diskB);
            $this->container->get('disk.service')->persist($diskB);
        }



        $this->em->persist($drbdDisk);
        $this->em->flush();
    }

}

?>
