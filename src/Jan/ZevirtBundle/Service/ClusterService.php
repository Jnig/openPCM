<?php

namespace Jan\ZevirtBundle\Service;

use Jan\ZevirtBundle\Model\Ssh;
use Jan\ZevirtBundle\Entity\Cluster;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\Node;

class ClusterService {

    private $em;
    protected $container;

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    public function persist(Cluster $cluster) {
        $this->em->persist($cluster);
        $this->em->flush();

        if ($this->container->get('control.service')->isCli()) {
            $this->persistTasks($cluster);
        } else {
            $this->container->get('job.service')->clusterPersist($cluster);
        }
    }

    public function remove(Cluster $cluster) {

        if ($this->container->get('control.service')->isCli()) {
            $this->removeTasks($cluster);
        } else {
            $this->container->get('job.service')->clusterRemove($cluster);
        }
    }

    private function persistTasks(Cluster $cluster) {
        foreach ($cluster->getNodes() as $node) {
            foreach ($cluster->getStorages() as $storage) {
                $this->container->get('node.service')->defineStorage($node, $storage);
            }
        }
    }

    private function removeTasks(Cluster $cluster) {
        $nodes = $cluster->getNodes();
        if (count($nodes)) {
            throw new \Exception("Can't delete cluster, because it has nodes associated to it");
        }

        $this->em->remove($cluster);
        $this->em->flush();
    }

    public function setMaintenanceMode(Node $node, $value) {
        foreach ($node->getCluster()->getNodes() as $node) {
            try {
                $this->container->get('node.service')->getConnection($node)->exec('crm_attribute --name maintenance-mode --update ' . $value);
                break;
            } catch (\Exception $e) {
                
            }
        }
    }

    public function verfiyClusterConfig(Node $node) {
        $out = $this->container->get('control.service')->getOutputWriter();
        try {
            $this->container->get('node.service')->getConnection($node)->exec('crm_verify  -L -V');
        } catch (\Exception $e) {
            $out->writeln("Cluster has errors and can't run resources.\n" . $e->getMessage());

            return 0;
        }

        return 1;
    }

    public function cibadminAdd(Cluster $cluster, $content) {
        $out = $this->container->get('control.service');

        foreach ($cluster->getNodes() as $node) {
            try {
                $this->container->get('node.service')->cibadminAdd($node, $content);
                break;
            } catch (\Exception $e) {
                
            }
        }
    }

    public function cibadminDelete(Cluster $cluster, $content) {
        $out = $this->container->get('control.service');


        foreach ($cluster->getNodes() as $node) {
            try {
                $this->container->get('node.service')->cibadminDelete($node, $content);
                break;
            } catch (\Exception $e) {
                
            }
        }
    }

}

