<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\Storage;
use Jan\ZevirtBundle\Form\StorageType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Jan\ZevirtBundle\Entity\StorageDir;
use Jan\ZevirtBundle\Form\StorageDirType;
use Jan\ZevirtBundle\Entity\StorageLogical;
use Jan\ZevirtBundle\Form\StorageLogicalType;
use Jan\ZevirtBundle\Entity\StorageIscsi;
use Jan\ZevirtBundle\Form\StorageIscsiType;
use Jan\ZevirtBundle\Entity\StorageNetfs;
use Jan\ZevirtBundle\Form\StorageNetfsType;
use Jan\ZevirtBundle\Entity\StorageRbd;
use Jan\ZevirtBundle\Form\StorageRbdType;
use Jan\ZevirtBundle\Entity\DiskIscsi;

class StorageController extends Controller {

    public function postStoragesAction() {

        return $this->processForm();
    }

    private function processForm($id = 0) {
        $object = json_decode($this->get("request")->getContent());
        $em = $this->getDoctrine()->getEntityManager();

        switch ($object->entity) {
            case 'StorageDir':
                if ($id) {
                    $entity = $em->getRepository('JanZevirtBundle:StorageDir')->findOneById($id);
                } else {
                    $entity = new StorageDir();
                }

                $type = new StorageDirType();
                break;
            case 'StorageLogical':
                if ($id) {
                    $entity = $em->getRepository('JanZevirtBundle:StorageLogical')->findOneById($id);
                } else {
                    $entity = new StorageLogical();
                }

                $type = new StorageLogicalType();
                break;
            case 'StorageIscsi':

                if ($id) {
                    $entity = $em->getRepository('JanZevirtBundle:StorageIscsi')->findOneById($id);
                } else {
                    $entity = new StorageIscsi();
                }

                $type = new StorageIscsiType();
                break;
            case 'StorageNetfs':
                if ($id) {
                    $entity = $em->getRepository('JanZevirtBundle:StorageNetfs')->findOneById($id);
                } else {
                    $entity = new StorageNetfs();
                }

                $type = new StorageNetfsType();
                break;
            case 'StorageRbd':
                if ($id) {
                    $entity = $em->getRepository('JanZevirtBundle:StorageRbd')->findOneById($id);
                } else {
                    $entity = new StorageRbd();
                }

                $type = new StorageRbdType();
                break;
            default:
                throw new Exception("Unknown storage");
                break;
        }


        $form = $this->createForm($type, $entity);

        $form->bind($this->getRequest());

        if ($form->isValid()) {

            $em->persist($entity);
            $em->flush();

            $view = View::create();
            return $view->setData(array($entity->toArray()));
        } else {
            return View::create($form, 400);
        }
    }

    public function putStoragesAction($id) {
        return $this->processForm($id);
    }

    /**
     * @Rest\View
     */
    public function getStoragesAction() {
        $em = $this->getDoctrine()->getManager();

        $ret = array();
        $entities = $em->getRepository('JanZevirtBundle:Storage')->findAll();
        foreach ($entities as $entity) {
            $ret[] = $entity->toArray();
        }

        return $ret;
    }

    /**
     * @Rest\View
     */
    public function getStoragesVirtualmachinesAction(VirtualMachine $vm) {
        $em = $this->getDoctrine()->getManager();

        $ret = $vm->getNode()->getStorages();


        return $ret;
    }

    /**
     * @Rest\View
     */
    public function getStoragesVolumesAction(storage $storage, VirtualMachine $vm) {
        $em = $this->getDoctrine()->getManager();

        $node = $vm->getNode();

        $ret = array();
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('JanZevirtBundle:Disk')->findBy(array('storage' => $storage, 'node' => $node));


        foreach ($result as $row) {
            $ret[] = $row->toArray();
        }

        return $ret;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteStoragesAction(Storage $entity) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @Rest\View
     */
    public function getStoragesDisksAction(storage $storage) {
        //$this->get('storage.service')->getStorageDirVolumes($storage);

        $ret = array();
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('JanZevirtBundle:Disk')->findByStorage($storage);


        foreach ($result as $row) {
            $ret[] = $row->toArray();
        }

        return $ret;
    }

}
