<?php

namespace Jan\ZevirtBundle\Service;

use Jan\ZevirtBundle\Entity\DiskLogical;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\DiskDrbd;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Jan\ZevirtBundle\Entity\Job;
use Jan\ZevirtBundle\Entity\Disk;
use Jan\ZevirtBundle\Entity\Cluster;

class JobService {

    private $em;
    protected $container;

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    public function postPersist(Job $job) {
        if (!$this->container->get('control.service')->isCli()) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            if (count($user)) {
                $job->setUser($user);
            }
            $this->em->persist($job);
            $this->em->flush();
        }
    }

    public function virtualmachinePersist(VirtualMachine $vm) {
        $job = new Job('zevirt:vm', array('persist', $vm->getId()));
        $job->setName("Save {$vm->getName()} to the cluster");
        $this->em->persist($job);
        $this->em->flush();
    }

    public function virtualmachineRemove(VirtualMachine $vm) {
        $job = new Job('zevirt:vm', array('remove', $vm->getId()));
        $job->setName("Remove {$vm->getName()} from the cluster");
        $this->em->persist($job);
        $this->em->flush();
    }

    public function diskPersist(Disk $disk) {
        $job = new Job('zevirt:disk', array('persist', $disk->getId()));
        $job->setName("Setup new disk {$disk->getPath()}");
        $this->em->persist($job);
        $this->em->flush();
    }

    public function diskRemove(Disk $disk) {
        $job = new Job('zevirt:disk', array('remove', $disk->getId()));
        $job->setName("Remove disk {$disk->getPath()}");

        $this->em->persist($job);
        $this->em->flush();
    }

    public function nodePersist(Node $node) {
        $job = new Job('zevirt:node', array('persist', $node->getId()));
        $job->setName("Setup node {$node->getName()}");
        $this->em->persist($job);
        $this->em->flush();
    }

    public function nodeRemove(Node $node) {
        $job = new Job('zevirt:node', array('remove', $node->getId()));
        $job->setName("Remove node {$node->getName()}");

        $this->em->persist($job);
        $this->em->flush();
    }

    public function nodeStandbyOn(Node $node) {
        $job = new Job('zevirt:node', array('standbyon', $node->getId()));
        $job->setName("Set node {$node->getName()} to standby mode");

        $this->em->persist($job);
        $this->em->flush();
    }

    public function nodeStandbyOff(Node $node) {
        $job = new Job('zevirt:node', array('standbyoff', $node->getId()));
        $job->setName("Set node {$node->getName()} back to normal mode");

        $this->em->persist($job);
        $this->em->flush();
    }

    public function clusterPersist(Cluster $cluster) {
        $job = new Job('zevirt:cluster', array('persist', $cluster->getId()));
        $job->setName("Setup cluster {$cluster->getName()}");
        $this->em->persist($job);
        $this->em->flush();
    }

    public function clusterRemove(Cluster $cluster) {
        $job = new Job('zevirt:cluster', array('remove', $cluster->getId()));
        $job->setName("Remove cluster {$cluster->getName()}");

        $this->em->persist($job);
        $this->em->flush();
    }

}

?>
