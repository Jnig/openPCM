<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\NetworkInterface;
use Jan\ZevirtBundle\Form\NetworkInterfaceType;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class InterfaceController extends Controller {

    public function postInterfacesAction(VirtualMachine $vm) {

        $entity = new NetworkInterface;
        return $this->processForm($vm, $entity);
    }

    private function processForm($vm, NetworkInterface $entity) {
        $form = $this->createForm(new NetworkInterfaceType, $entity);


        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            if ($entity->getMacAddress() == '[auto]') {
                $mac = $em->getRepository('JanZevirtBundle:NetworkInterface')->getRandomUnusedMac();
                $entity->setMacAddress($mac);
            }


            $em->persist($entity);
            $em->flush();

            $vm->addNetworkInterface($entity);
            $this->get('virtualmachine.service')->persist($vm);

            $view = View::create();
            $view->setData($entity->toArray());
            return $this->get('fos_rest.view_handler')->handle($view);
        } else {
            return View::create($form, 400);
        }
    }

    public function putInterfacesAction(VirtualMachine $vm, NetworkInterface $entity) {

        return $this->processForm($vm, $entity);
    }

    /**
     * @Rest\View
     */
    public function getInterfacesAction(VirtualMachine $vm) {
        $em = $this->getDoctrine()->getManager();

        $entities = $vm->getNetworkInterfaces();

        $ret = array();
        foreach ($entities as $entity) {
            $ret[] = $entity->toArray();
        }

        return $ret;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteInterfacesAction(VirtualMachine $vm, NetworkInterface $entity) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->get('virtualmachine.service')->persist($vm);
    }

}
