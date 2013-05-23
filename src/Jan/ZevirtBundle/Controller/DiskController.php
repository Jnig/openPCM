<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\DiskIscsi;
use Jan\ZevirtBundle\Form\DiskIscsiType;
use Jan\ZevirtBundle\Entity\Disk;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Jan\ZevirtBundle\Form\DiskCreateType;
use Jan\ZevirtBundle\Form\DiskOtherType;
use Jan\ZevirtBundle\Form\DiskDrbdType;
use Jan\ZevirtBundle\Form\DiskEditType;
use Jan\ZevirtBundle\Form\DiskExistingType;
use Jan\ZevirtBundle\Entity\Job;

class DiskController extends Controller {

    /**
     * @Rest\View
     */
    public function postDisksAction(VirtualMachine $vm) {
        $post = json_decode($this->get("request")->getContent());

        $em = $this->getDoctrine()->getEntityManager();
        //existing Storage
        if ($post->action == 'existing') {

            $disk = $em->getRepository('JanZevirtBundle:Disk')->findOneById($post->volume);

            $form = $this->createForm(new DiskExistingType, $disk);
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $vm->addDisk($disk);

                $em->persist($vm);
                $em->flush();

                return $disk->toArray();
            } else {
                return $form;
            }
        } elseif ($post->action == 'create') {
            $storage = $em->getRepository('JanZevirtBundle:Storage')->findOneById($post->storage);
            $disk = $storage->getDiskEntity();

            $form = $this->createForm(new DiskCreateType, $disk);
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $disk->setStorage($storage);
                $disk->setNode($vm->getNode());
                $disk->addVirtualmachine($vm);
                $disk->setCreated(false);
                $disk->setCapacity($disk->getCapacity() * 1024 * 1024 * 1024);

                $this->get('disk.service')->persist($disk);


                return $disk->toArray();
            } else {
                return $form;
            }
        } elseif ($post->action == 'other') {
            $disk = new \Jan\ZevirtBundle\Entity\DiskFile;

            $form = $this->createForm(new DiskOtherType, $disk);
            $form->bind($this->getRequest());
            if ($form->isValid()) {

                $disk->setNode($vm->getNode());
                $disk->addVirtualmachine($vm);

                $em->persist($disk);
                $em->flush();


                return $disk->toArray();
            } else {
                return $form;
            }
        } elseif ($post->action == 'drbd') {
            $disk = new \Jan\ZevirtBundle\Entity\DiskDrbd;

            $form = $this->createForm(new DiskDrbdType, $disk);
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $disk->setDriverType('raw');
                $disk->addVirtualmachine($vm);
                $disk->setCapacity($disk->getCapacity() * 1024 * 1024 * 1024);

                $this->get('disk.service')->persist($disk);




                return $disk->toArray();
            } else {
                return $form;
            }
        }
    }

    public function putDisksAction(VirtualMachine $vm, Disk $entity) {

        $form = $this->createForm(new DiskEditType, $entity);

        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();

            $em->persist($entity);
            $em->flush();

            $view = View::create();
            return $view->setData($entity->toArray());
        } else {
            return View::create($form, 400);
        }
    }

    /**
     * @Rest\View
     */
    public function getDisksAction(VirtualMachine $vm) {
        $em = $this->getDoctrine()->getManager();

        $entities = $vm->getDisks();

        $ret = array();
        foreach ($entities as $entity) {
            if (!$entity->getChild()) {
                $ret[] = $entity->toArray();
            }
        }


        return $ret;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteDisksAction(VirtualMachine $vm, Disk $entity) {
        if ($this->get('virtualmachine.service')->isRunning($vm)) {
            throw new \Jan\ZevirtBundle\Model\ZevirtException("Stop Virtualmachine before removing disks");
        }

        $this->get('disk.service')->remove($entity);
    }

    /**
     * @Rest\View
     */
    public function getHardwaresAction(VirtualMachine $vm) {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Disk')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

}
