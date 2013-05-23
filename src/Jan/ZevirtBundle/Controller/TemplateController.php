<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Form\TemplateType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class TemplateController extends Controller {

    public function postTemplatesAction() {
        $entity = new Template;
        return $this->processForm($entity);
    }

    private function processForm(Template $entity) {
        $form = $this->createForm(new TemplateType, $entity);

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

    public function putTemplatesAction(Template $entity) {
        return $this->processForm($entity);
    }

    /**
     * @Rest\View
     */
    public function getTemplatesAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Template')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteTemplatesAction(Template $entity) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @Rest\View
     */
    public function getTemplatesVirtualmachineAction(\Jan\ZevirtBundle\Entity\VirtualMachine $vm) {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Template')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

}
