<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\Network;
use Jan\ZevirtBundle\Form\NetworkType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class NetworkController extends Controller {

    public function postNetworksAction() {
        $entity = new Network;
        return $this->processForm($entity);
    }

    private function processForm(Network $entity) {
        $form = $this->createForm(new NetworkType, $entity);

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

    public function putNetworksAction(Network $entity) {
        return $this->processForm($entity);
    }

    /**
     * @Rest\View
     */
    public function getNetworksAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Network')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteNetworksAction(Network $entity) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @Rest\View
     */
    public function getNetworksVirtualmachineAction(\Jan\ZevirtBundle\Entity\VirtualMachine $vm) {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Network')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

}
