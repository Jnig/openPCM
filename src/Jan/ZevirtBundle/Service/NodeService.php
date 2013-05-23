<?php

namespace Jan\ZevirtBundle\Service;

use Jan\ZevirtBundle\Model\Ssh;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Model\ConnectionException;
use Jan\ZevirtBundle\Entity\StorageRbd;

class NodeService {

    private $em;
    protected $container;

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    public function getConnection(Node $node) {
        return $this->container->get('connection.service')->getConnection($node);
    }

    public function defineVirtualmachine(Node $node, VirtualMachine $vm) {
        $out = $this->container->get('control.service');
        try {
            $out->writeln($node, "virsh define vm_{$vm->getId()}.xml");

            $this->getConnection($node)->exec('mkdir -p /var/lib/libvirt/definitions/');


            $fileName = '/var/lib/libvirt/definitions/vm_' . $vm->getId() . '.xml';

            $this->getConnection($node)
                    ->filePutContents($fileName, $this->container->get('virtualmachine.service')->toXml($vm))
                    ->exec('virsh define ' . $fileName)
            ;





            if ($vm->getUuid() == '') {
                $uuid = $this->getConnection($node)->exec('virsh domuuid ' . $vm->getRealName());

                $uuid = trim($uuid);

                if (strpos($uuid, 'error') !== false) {
                    throw new \Exception('virsh domuuid failed');
                } else {
                    $vm->setUuid($uuid);
                }

                $this->em->persist($vm);
            }
        } catch (ConnectionException $e) {
            $out->writeln($node, "Can't connect to node. Marking node as dirty!");

            $node->setDirty(true);
            $this->em->persist($node);
        }
        $this->em->flush();
    }

    public function undefineVirtualmachine(Node $node, VirtualMachine $vm) {
        $out = $this->container->get('control.service');
        try {

            $out->writeln("Undefining {$vm->getName()} Libvirt definitions from node {$node->getName()}");

            $this->getConnection($node)->exec('virsh undefine ' . $vm->getRealName());

            if ($this->getConnection($node)->fileExists('/etc/libvirt/qemu/' . $vm->getRealName() . '.xml')) {
                $out->writeln('Libvirt undefine failed on node ' . $node->getName());
            }
        } catch (ConnectionException $e) {

            $out->writeln($node, "Can't connect to node. Marking node as dirty!");

            $node->setDirty(true);
            $this->em->persist($node);
        }
        $this->em->flush();
    }

    public function undefineStorage(Node $node, Storage $storage) {
        $out = $this->container->get('control.service');
        try {
            if ($storage instanceof StorageRbd) {
                $this->getConnection($node)->exec('virsh secret-undefine ' . $storage->getSecretUuid());
            }

            $out->writeln("Undefining {$storage->getName()} from node {$node->getName()}");

            if ($storage->toXml() != '') {
                $this->getConnection($node)->exec('virsh pool-undefine ' . $storage->getRealName());
            }
        } catch (ConnectionException $e) {

            $out->writeln($node, "Can't connect to node. Marking node as dirty!");

            $node->setDirty(true);
            $this->em->persist($node);
        }
        $this->em->flush();
    }

    public function cibadminAdd(Node $node, $content) {
        $out = $this->container->get('control.service');



        $out->writeln($node, "Updating Pacemaker Configuration");

        $filename = '/tmp/' . uniqid();

        $this->getConnection($node)->filePutContents($filename, $content);

        try {
            $this->getConnection($node)->exec('cibadmin  -c -M --xml-file ' . $filename);
        } catch (\Exception $e) {
            $out->writeln($e->getMessage());
        }


        //$this->getConnection($node)->exec('rm '.$filename);
    }

    public function cibadminDelete(Node $node, $xml) {
        $out = $this->container->get('control.service');



        $out->writeln($node, "Updating Pacemaker Configuration");


        try {
            $this->getConnection($node)->exec('cibadmin --delete --xml-text ' . escapeshellarg($xml));
        } catch (\Exception $e) {
            $out->writeln($e->getMessage());
        }


        //$this->getConnection($node)->exec('rm '.$filename);
    }

    public function getShortHostname($node) {
        return $this->getConnection($node)->exec('uname -n');
    }

    public function getNodeInterfaces($node, $index = 0) {
        $out = $this->getConnection($node)->exec('ip addr');

        $splitted = preg_split("/\s+\d+: /", $out);
        $ret = array();
        $ret[] = array('interfaceName' => '-', 'ip' => '', 'cidr' => '', 'net' => '');
        foreach ($splitted as $split) {
            preg_match("/^(\w+\d+):.*inet (([\w\.]+)\/(\d+))/s", $split, $matches);

            if (count($matches)) {
                $net = long2ip((ip2long($matches[3])) & ((-1 << (32 - (int) $matches[4]))));
                if ($index) {
                    $ret[$matches[3]] = array('interfaceName' => $matches[1], 'ip' => $matches[3], 'cidr' => $matches[4], 'net' => $net);
                } else {
                    $ret[] = array('interfaceName' => $matches[1], 'ip' => $matches[3], 'cidr' => $matches[4], 'net' => $net);
                }
            }
        }

        return $ret;
    }

    public function persist(Node $node) {
        $this->em->persist($node);
        $this->em->flush();

        if ($this->container->get('control.service')->isCli()) {
            $this->persistTasks($node);
        } else {
            $this->container->get('job.service')->nodePersist($node);
        }
    }

    public function remove(Node $node) {
        if ($this->container->get('control.service')->isCli()) {
            $this->removeTasks($node);
        } else {
            $this->container->get('job.service')->nodeRemove($node);
        }
    }

    private function persistTasks(Node $node) {
        $out = $this->container->get('control.service');




        $out->writeln($node, 'Get ssh public key');
        $public = $this->getPublicKey($node);
        if ($public != $node->getPublicKey()) {
            $out->writeln('Found new Public key');
            $node->setPublicKey($public);

            $this->em->persist($node);
            $this->em->flush();

            foreach ($node->getCluster()->getNodes() as $node2) {
                $this->saveAuthorizedKeys($node2);
            }
        }

        $this->verifySshConfig($node);

        if ($node->getCorosyncIp1() != '' || $node->getCorosyncIp2() != '') {
            $corosyncConfig = $this->getCorosyncConfig($node);
            $corosyncConfigOld = $this->getConnection($node)->fileGetContents('/etc/corosync/corosync.conf');
            if ($corosyncConfig != $corosyncConfigOld) { //str compare doesn't work?!
                $out->writeln('New corosync config found');


                foreach ($node->getCluster()->getNodes() as $node2) {



                    $out->writeln($node2, 'writing corosync config');
                    $this->getConnection($node2)->exec('mv /etc/corosync/corosync.conf /etc/corosync/corosync.conf.bak');
                    $this->getConnection($node2)->filePutContents('/etc/corosync/corosync.conf', $this->getCorosyncConfig($node2));

                    if ($this->isRunningProcess($node2, 'pacemaker')) {
                        $out->writeln($node2, 'Ensure cluster is in maintenance');
                        $this->container->get('cluster.service')->setMaintenanceMode($node2, 1);

                        $cmd = '/etc/init.d/pacemaker stop';
                        $out->writeln($node2, $cmd);
                        $this->getConnection($node2)->exec($cmd);
                    }

                    if ($this->isRunningProcess($node2, 'corosync')) {
                        $cmd = '/etc/init.d/corosync stop';
                        $out->writeln($node2, $cmd);
                        $this->getConnection($node2)->exec($cmd);
                    }
                    $cmd = '/etc/init.d/corosync start';
                    $out->writeln($node2, $cmd);
                    $this->getConnection($node2)->exec($cmd);

                    $cmd = '/etc/init.d/pacemaker start';
                    $out->writeln($node2, $cmd);
                    $this->getConnection($node2)->exec($cmd);
                }

                sleep(20); //wait for pacemaker full startup

                $out->writeln($node, 'Bringing cluster back to normal mode');
                $this->container->get('cluster.service')->setMaintenanceMode($node, 0);
            }

            $this->pacemakerAdd($node);
        }

        foreach ($node->getStorages() as $storage) {
            $this->defineStorage($node, $storage);
        }
    }

    private function removeTasks(Node $node) {
        $out = $this->container->get('control.service');

        $cluster = $node->getCluster();

        if ($this->isRunningProcess($node, 'pacemaker')) {
            $out->writeln($node, 'Ensure cluster is in maintenance');
            $this->container->get('cluster.service')->setMaintenanceMode($node, 1);


            //$out->writeln($node, 'Delete pacemaker config');  
            //$this->getConnection($node)->exec('cibadmin -E --force');

            $cmd = '/etc/init.d/pacemaker stop';
            $out->writeln($node, $cmd);
            $this->getConnection($node)->exec($cmd);
        }

        if ($this->isRunningProcess($node, 'corosync')) {
            $cmd = '/etc/init.d/corosync stop';
            $out->writeln($node, $cmd);
            $this->getConnection($node)->exec($cmd);
        }

        $cmd = 'rm /etc/corosync/corosync.conf';
        $out->writeln($node, $cmd);
        try {
            $this->getConnection($node)->exec($cmd);
        } catch (\Exception $e) {
            
        }

        $this->em->remove($node);
        $this->em->flush();

        $nodes = $cluster->getNodes();
        if (count($nodes)) {
            if ($this->isRunningProcess($nodes[0], 'pacemaker')) {
                $out->writeln($nodes[0], 'Bringing cluster back to normal mode');
                $this->container->get('cluster.service')->setMaintenanceMode($nodes[0], 0);
            }
        }
    }

    public function verifySshConfig($node) {
        $config = '#added by openPCM for live migration DO NOT MODIFY
Host * 
   StrictHostKeyChecking no 
#added by openPCM for live migration DO NOT MODIFY
';
//UserKnownHostsFile=/dev/null

        $connection = $this->getConnection($node);
        $connection->exec('mkdir -p /root/.ssh/');
        $content = $connection->fileGetContents('/root/.ssh/config');
        if (strpos($content, $config) === false) {
            $content = $config . $content;
            $connection->filePutContents('/root/.ssh/config', $content);
        }
    }

    public function getCorosyncConfig(Node $node) {
        if ($node->getCorosyncIp1() == '' && $node->getCorosyncIp2() == '') {
            return '';
        }


        $interfaces1 = '';
        $interfaces2 = '';


        $nodes = $this->em->getRepository('JanZevirtBundle:Node')->findBy(array('cluster' => $node->getCluster()), array('id' => 'asc')); // always default node order 


        foreach ($nodes as $node2) {

            if ($node->getCorosyncIp1() != '') {
                if ($node2->getCorosyncIp1() != '') {
                    $interfaces1 .= "member {
                                                memberaddr: {$node2->getCorosyncIp1()}
                                     }
                                     ";
                }
            }

            if ($node->getCorosyncIp2() != '') {
                if ($node2->getCorosyncIp2() != '') {
                    $interfaces2 .= "member {
                                                memberaddr: {$node2->getCorosyncIp2()}
                                     }
                                     ";
                }
            }
        }

        $interfaces = $this->getNodeInterfaces($node, 1);

        if (!empty($interfaces1)) {
            $interfaces1 = "interface {
                    $interfaces1
                    ringnumber: 0
                    bindnetaddr: {$interfaces[$node->getCorosyncIp1()]['net']}	
                    mcastport: 5405
                    }";
        }
        if (!empty($interfaces2)) {
            $interfaces2 = "interface {
                    $interfaces2
                    ringnumber: 1
                    bindnetaddr: {$interfaces[$node->getCorosyncIp2()]['net']}
                    mcastport: 5406
                    }";
        }


        $config = "
totem {
	version: 2

	# How long before declaring a token lost (ms)
	token: 3000

	# How many token retransmits before forming a new configuration
	token_retransmits_before_loss_const: 10

	# How long to wait for join messages in the membership protocol (ms)
	join: 60

	# How long to wait for consensus to be achieved before starting a new round of membership configuration (ms)
	consensus: 3600

	# Turn off the virtual synchrony filter
	vsftype: none

	# Number of messages that may be sent by one processor on receipt of the token
	max_messages: 20

	# Limit generated nodeids to 31-bits (positive signed integers)
	clear_node_high_bit: yes

	# Disable encryption
 	secauth: off

	# How many threads to use for encryption/decryption
 	threads: 0

	# Optionally assign a fixed node id (integer)
	# nodeid: 1234

	# This specifies the mode of redundant ring, which may be none, active, or passive.
 	rrp_mode: active

        $interfaces1
        $interfaces2
	transport: udpu
}

amf {
	mode: disabled
}

service {
 	# Do not Load the Pacemaker Cluster Resource Manager
 	ver:       1
 	name:      pacemaker
}

aisexec {
        user:   root
        group:  root
}
logging {
	fileline: off
	to_stderr: no
	to_logfile: yes
	to_syslog: yes
	logfile: /var/log/corosync.log
	debug: off
	timestamp: on
        logger_subsys {
                subsys: AMF
                debug: off
                tags: enter|leave|trace1|trace2|trace3|trace4|trace6
        }
}


";
        return $config;
    }

    public function pacemakerAdd(Node $node) {


        $id = $node->getRealName();
        $params = $node->getStonithParameters();

        if (!empty($params)) {
            $this->getConnection($node)->exec('crm_attribute --name stonith-enabled --update true');
            $params = trim($params);
            $params = str_replace('"', '', $params);
            $params = explode("\n", $params);
            $nvpair = '';

            foreach ($params as $param) {
                if (strpos($param, '=') !== false) {
                    list ($name, $value) = explode('=', $param);
                    if (!empty($name) && !empty($value)) {
                        $name = trim($name);
                        $value = trim($value);

                        $nvpair .= '<nvpair id="st-' . $id . '-instance_attributes-' . $name . '" name="' . $name . '" value="' . $value . '"/>' . "\n";
                    }
                }
            }

            $text = '

     <configuration>
     <resources>
        <primitive class="stonith" id="st-' . $id . '" type="' . $node->getStonith() . '">
          <instance_attributes id="st-' . $id . '-instance_attributes">
          ' . $nvpair . '
          </instance_attributes>
        </primitive>
    </resources>
      <constraints>
        <rsc_location id="l-st-' . $id . '" node="' . $this->getShortHostname($node) . '" rsc="st-' . $id . '" score="-INFINITY"/>
      </constraints>
    </configuration>

    ';

            $this->cibadminAdd($node, $text);
        }
    }

    public function pacemakerDelete(Node $node) {
        $content = "
delete st-node{$node->getId()}
delete l-st-node{$node->getId()}
";

        // first delete constraint then vm, otherwise cibadmin fails
        $this->cibadminDelete($node, '<rsc_location id="l-' . $resourceName . '" />');
        $this->cibadminDelete($node, '<primitive class="ocf" id="' . $resourceName . '" />');
    }

    public function getPublicKey(Node $node) {
        $public = trim($this->getConnection($node)->fileGetContents('/root/.ssh/id_rsa.pub'));


        if (empty($public)) {
            $this->getConnection($node)->exec('mkdir -p /root/.ssh/');
            $this->getConnection($node)->exec('ssh-keygen -N "" -f /root/.ssh/id_rsa');
            $public = $this->getConnection($node)->fileGetContents('/root/.ssh/id_rsa.pub');
        }

        return $public;
    }

    public function saveAuthorizedKeys(Node $node) {
        $nodes = $node->getCluster()->getNodes();
        $authorizedKeys = array();
        foreach ($nodes as $node2) {
            if ($node->getId() != $node2->getId()) {
                $authorizedKeys[] = $node2->getPublicKey();
            }
        }

        $this->getConnection($node)->addSshKey('/root/.ssh/authorized_keys', $authorizedKeys);
    }

    public function defineStorage(Node $node, $storage) {
        if ($storage instanceof StorageRbd) {
            $this->defineStorageRbd($node, $storage);
        }

        $out = $this->container->get('control.service');

        $out->writeln($node, "Defining {$storage->getName()}");
        $this->getConnection($node)->exec('mkdir -p /var/lib/libvirt/definitions/');
        $fileName = '/var/lib/libvirt/definitions/storage_' . str_replace(" ", "_", $storage->getId()) . '.xml';

        if ($storage->toXml() != '') {
            $this->getConnection($node)->filePutContents($fileName, $storage->toXml());

            $this->getConnection($node)->exec('virsh pool-define ' . $fileName);

            if ($storage->getUuid() == '') {
                $uuid = $this->getConnection($node)->exec('virsh pool-uuid ' . escapeshellarg($storage->getRealName()));
                $storage->setUuid($uuid);
                $this->em->persist($storage);
                $this->em->flush();
            }

            try {
                $this->getConnection($node)->exec('virsh pool-start ' . escapeshellarg($storage->getRealName()));
                $this->getConnection($node)->exec('virsh pool-autostart ' . escapeshellarg($storage->getRealName()));
            } catch (\Exception $e) {
                
            }
        }
    }

    public function defineStorageRbd(Node $node, StorageRbd $storage) {
        $out = $this->container->get('control.service');

        $out->writeln($node, "Defining secret for {$storage->getName()}");

        if ($storage->toSecretDefineXml() != '') {
            $this->getConnection($node)->exec('mkdir -p /var/lib/libvirt/definitions/');
            $fileName = '/var/lib/libvirt/definitions/storage_' . str_replace(" ", "_", $storage->getId()) . '_secret.xml';

            $this->getConnection($node)->filePutContents($fileName, $storage->toSecretDefineXml());

            $out = $this->getConnection($node)->exec('virsh secret-define ' . $fileName);

            if ($storage->getSecretUuid() == '') {
                preg_match("/.* (.*) .*/", $out, $matches);

                $storage->setSecretUuid($matches[1]);

                $this->em->persist($storage);
                $this->em->flush();
            }

            $this->getConnection($node)->exec("virsh secret-set-value --secret {$storage->getSecretUuid()} --base64 {$storage->getSecret()}");
        }
    }

    public function getStonithDevices(Node $node) {
        $ret = array();
        $connection = $this->getConnection($node);
        $stonithDevices = $connection->exec('stonith_admin -I 2>&1');

        $stonithDevices = explode("\n", $stonithDevices);


        array_shift($stonithDevices);

        $devices = array_map('trim', $stonithDevices);
        sort($devices);
        foreach ($devices as $device) {
            $ret[] = array('stonith' => $device);
        }

        return $ret;
    }

    public function getStonithDevicesMetadata(Node $node, $stonithDevice) {


        $connection = $this->getConnection($node);

        $out = $connection->exec('stonith_admin --metadata -a ' . escapeshellarg($stonithDevice));

        $xml = new \SimpleXMLElement($out);

        $description = trim($xml->longdesc) . "\n\n";
        $params = '';
        foreach ($xml->parameters->parameter as $param) {
            $description .= $param['name'] . '=' . trim($param->shortdesc) . "\n";
            if ($param->content['type'] == 'boolean') {
                $value = 'false';
            } else {
                $value = '';
            }
            $params .= $param['name'] . '="' . $value . '"' . "\n";
        }

        $ret = array('stonith' => $stonithDevice, 'params' => $params, 'description' => $description);
        return $ret;
    }

    public function isRunningProcess(Node $node, $name) {
        $connection = $this->getConnection($node);
        $out = $connection->exec("ps aux | grep -v grep | grep " . escapeshellarg($name) . " > /dev/null && echo $?");

        if (trim($out) == '0') {
            return true;
        } else {
            return false;
        }
    }

    public function scanVirtualmachines(Node $node) {



        $out = $this->getConnection($node)->exec("virsh list  --all | tail -n +3");

        preg_match_all("/([\d\-]+)\s+(vm_(\d+))\s+(running|shut off|paused|crashed)$/m", $out, $matches);



        $clusterVmIds = array(); //all vm id's which are allowed on Node
        $query = $this->em->createQuery("Select a FROM JanZevirtBundle:VirtualMachine a 
                                         JOIN a.node b
                                         WHERE b.cluster = :cluster")->setParameter('cluster', $node->getCluster()->getId());
        $result = $query->getResult();
        foreach ($result as $vm) {
            $clusterVmIds[] = $vm->getId();
        }


        $vms = array();
        for ($i = 0; $i < count($matches[3]); $i++) {
            $id = $matches[3][$i];


            $vm = $this->em->getRepository('JanZevirtBundle:VirtualMachine')->findOneById($id);
            if (count($vm)) {
                if (in_array($id, $clusterVmIds) === false) { // virtualmachine exists but should not be defined on that cluster/node 
                    $this->undefineVirtualmachine($node, $vm);
                } else {

                    if ($matches[4][$i] == 'running') {
                        $vm->setState('running');
                        $vm->setNode($node);
                    } else if ($matches[4][$i] == 'shut off' && $node->getId() == $vm->getNode()->getId()) {
                        $vm->setState('shut off');
                    } else {
                        $vm->setState($matches[4][$i]);
                    }

                    $this->em->persist($vm);
                }
            } else { //orphaned vm, remove it
                $this->getConnection($node)->exec('virsh undefine vm_' . $id);
            }

            $vms[] = $id;
        }

        $this->em->flush();

        //check if all vms that should run in this cluster are also defined
        if ($node->isHa()) {
            $query = $this->em->createQuery("Select a FROM JanZevirtBundle:VirtualMachine a 
                                             JOIN a.node b
                                             WHERE b.cluster = :cluster")->setParameter('cluster', $node->getCluster()->getId());
            $result = $query->getResult();

            foreach ($result as $vm) {
                if (in_array($vm->getId(), $vms) === false) {
                    $this->defineVirtualmachine($node, $vm);
                }
            }
        }
    }

    public function scan(Node $node) {
        $this->scanVirtualmachines($node);
    }

    public function setStandby(Node $node, $value) {
        $out = $this->container->get('control.service');


        try {
            if ($value) {
                $out->writeln($node, "Setting node to standby mode");
                $this->getConnection($node)->exec('crm_standby -v true');
            } else {
                $out->writeln($node, "Setting node back to normal node");
                $this->getConnection($node)->exec('crm_standby -D');
            }
        } catch (\Exception $e) {
            $out->writeln($node, $e->getMessage());

            return;
        }

        $node->setStandby($value);
        $this->em->persist($node);
        $this->em->flush();
    }

}

