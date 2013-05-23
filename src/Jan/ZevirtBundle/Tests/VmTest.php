<?php

// src/Acme/DemoBundle/Tests/Utility/CalculatorTest.php
namespace Jan\ZevirtBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


use Doctrine\ORM\EntityManager;

use Jan\ZevirtBundle\Entity\VirtualMachine;

use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\Cluster;

class VmTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getEntityManager()
        ;
        $this->container = static::$kernel->getContainer();
        
        $this->get('control.service')->setCli();
        
        $this->get('connection.service')->setDriver('Jan\ZevirtBundle\Tests\Driver');
        

        

        
        $this->em->flush();

        
    }
    
    public function get($name) {
        return $this->container->get($name);
    }
    
    public function testGetRbdVolumes() {
        
    }
    
    public function testGetLvmVolumes() {


        $storage = $this->em->getRepository('JanZevirtBundle:Storage')->findOneByName('test-lvm-storage');
        $node = $this->em->getRepository('JanZevirtBundle:Node')->findOneByName('test-node');
        
        $this->get('storage.service')->getStorageVolumes($storage, $node);
        $disk = $this->em->getRepository('JanZevirtBundle:Disk')->findOneByPath('/dev/main/vmdisk_48');
        
        $this->assertEquals(1, count($disk));
        
    }
    
    public function testGetFileVolumes() {
        
    }
    
    public function testGetIscsiVolumes() {
        
    }
   
    
    /*public function testCreateCluster()
    {
        $this->writeln( "Create Cluster Test\n=================================\n");

        $cluster1 = $this->em->getRepository('JanZevirtBundle:Cluster')->findOneByName('unit-test-cluster1');
        if (!count($cluster1)) {
            $cluster1 = new Cluster;
        }

        $cluster1->setName('unit-test-cluster1');
        $cluster1->setHa(1);
        
        $cluster2 = $this->em->getRepository('JanZevirtBundle:Cluster')->findOneByName('unit-test-cluster2');
        if (!count($cluster2)) {
            $cluster2 = new Cluster;
        }
        $cluster2->setName('unit-test-cluster2');
        $cluster2->setHa(1);    

        
        $this->get('cluster.service')->persist($cluster1);
        $this->get('cluster.service')->persist($cluster2);        
            
    }*/
    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
    

}