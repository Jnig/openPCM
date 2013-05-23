<?php

namespace Jan\ZevirtBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Jan\ZevirtBundle\Service\VirtualmachineService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\Event;
use JMS\DiExtraBundle\Annotation\DoctrineListener;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @DoctrineListener(
 *     events = {"prePersist", "postPersist", "preUpdate", "postUpdate", "preRemove"},
 *     connection = "default",
 *     lazy = true,
 *     priority = 0
 * )
 * 
 */
class Entity2Node {

    protected $container;
    protected $eventsArray = array('VirtualMachine', 'Job', 'Disk', 'Node');

    /**
     * @InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $args) {
        
    }

    public function postPersist(LifecycleEventArgs $args) {
        $this->saveEvents('insert', $args->getEntity(), $args->getEntityManager());

        $entity = $args->getEntity();


        if (get_class($entity) == 'Jan\ZevirtBundle\Entity\VirtualMachine') {
            //$this->container->get('virtualmachine.service')->postPersist($entity);
        }

        if (get_class($entity) == 'Jan\ZevirtBundle\Entity\DiskDrbd') {
            $this->container->get('drbd.service')->postPersist($entity);
        }

        if (get_class($entity) == 'Jan\ZevirtBundle\Entity\Job') {

            $this->container->get('job.service')->postPersist($entity);
        }

        /*
          if (get_parent_class($entity) == 'Jan\ZevirtBundle\Entity\Disk') {
          if ($entity->getCreated() == false) {
          $this->container->get('storage.service')->create($entity);
          }

          if (count($entity->getVirtualmachine())) {
          foreach ($entity->getVirtualmachine() as $vm) {
          $this->container->get('virtualmachine.service')->preUpdate($vm);
          }

          }

          } */
    }

    public function preUpdate(PreUpdateEventArgs $args) {
        $entity = $args->getEntity();


        if (get_class($entity) == 'Jan\ZevirtBundle\Entity\VirtualMachine') {
            //$this->container->get('virtualmachine.service')->preUpdate($args);
        }

        /*
          }  elseif (get_class($entity)  == 'Jan\ZevirtBundle\Entity\DiskDrbd') {
          $this->container->get('drbd.service')->preUpdate($entity);

          }

          if (get_parent_class($entity) == 'Jan\ZevirtBundle\Entity\Disk') {


          if (count($entity->getVirtualmachine())) {
          foreach ($entity->getVirtualmachine() as $vm) {
          $this->container->get('virtualmachine.service')->preUpdate($vm);
          }
          }

          } */
    }

    public function postUpdate(LifecycleEventArgs $args) {
        $entity = $args->getEntity();


        if (get_class($args->getEntity()) == 'JMS\JobQueueBundle\Entity\Job') {
            if ($args->getEntity()->getState() != 'running') {
                $em = $this->container->get('doctrine')->getManager();
                $entity = $em->getRepository('JanZevirtBundle:Job')->findOneByJob($args->getEntity()->getId());

                $this->saveEvents('update', $entity, $args->getEntityManager());
            }
        }

        $this->saveEvents('update', $args->getEntity(), $args->getEntityManager());
    }

    public function preRemove(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if (get_class($entity) == 'Jan\ZevirtBundle\Entity\VirtualMachine') {
            //$this->container->get('virtualmachine.service')->preRemove($args);
        } elseif (get_class($entity) == 'Jan\ZevirtBundle\Entity\NetworkInterface') {
            //$this->container->get('virtualmachine.service')->preUpdate($entity->getVirtualmachine());
        } elseif (get_class($entity) == 'Jan\ZevirtBundle\Entity\DiskDrbd') {
            //$this->container->get('drbd.service')->preRemove($entity);
        }

        /* if (get_parent_class($entity) == 'Jan\ZevirtBundle\Entity\Disk') {
          if (count($entity->getVirtualmachine())) {
          foreach ($entity->getVirtualmachine() as $vm) {
          $this->container->get('virtualmachine.service')->preUpdate($vm);
          }
          }

          } */
    }

    public function postRemove(LifecycleEventArgs $args) {
        $this->saveEvents('remove', $args->getEntity(), $args->getEntityManager());
    }

    public function saveEvents($action, $entity, $em) {

        $em = $this->container->get('doctrine')->getManager();
        $class = get_parent_class($entity);
        if (empty($class)) {
            $class = get_class($entity);
        }

        if (strpos($class, 'Zevirt') !== false) {

            $class = preg_replace('#^.*\\\#', '', $class);
            if (in_array($class, $this->eventsArray)) {

                $event = new Event;
                $event->setAction($action);
                $event->setEntity($class);
                $event->setEntityId($entity->getId());


                $em->persist($event);
                $em->flush();
            }
        }
    }

}