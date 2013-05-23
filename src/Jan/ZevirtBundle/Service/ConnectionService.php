<?php

namespace Jan\ZevirtBundle\Service;

use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Model\ConnectionException;
use Jan\ZevirtBundle\Entity\StorageRbd;

class ConnectionService {

    private $em;
    protected $container;
    private $driver = 'Jan\ZevirtBundle\Model\Ssh';

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    private $connections = array();

    public function getConnection(Node $node) {
        if (!isset($this->connections[$node->getId()])) {
            $ssh = new $this->driver();


            if ($this->driver == 'Jan\ZevirtBundle\Model\Ssh') {
                $ssh->setIp($node->getHostname());
                $ssh->setUser('root');

                if ($node->getPassword() != '') {

                    $ssh->setPassword($node->getPassword());
                }

                try {
                    $ssh->connect();
                } catch (ConnectionException $e) {
                    $out = $this->container->get('control.service');
                    $out->writeln($node, "Can't connect to node. Marking node as dirty!");

                    $node->setDirty(true);
                    $this->em->persist($node);
                    $this->em->flush();
                }
            }
            $this->connections[$node->getId()] = $ssh;
        }



        return $this->connections[$node->getId()];
    }

    public function setDriver($driver) {
        $this->driver = $driver;
    }

    public function getDriver() {
        return $this->driver;
    }

}

