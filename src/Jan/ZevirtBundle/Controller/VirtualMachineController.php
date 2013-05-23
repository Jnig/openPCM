<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Jan\ZevirtBundle\Form\VirtualMachineType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Jan\ZevirtBundle\Entity\Job;
use Jan\ZevirtBundle\Entity\Node;

class VirtualMachineController extends Controller {

    public function postVmsAction() {
        $entity = new VirtualMachine;
        return $this->processForm($entity);
        //return View::create(array('success' => false, 'error' => 'foo'));
    }

    private function processForm(VirtualMachine $entity) {
        $form = $this->createForm(new VirtualMachineType, $entity);



        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();

            $entity->setNodeDefault($entity->getNode());


            $this->get('virtualmachine.service')->persist($entity);



            $view = View::create();
            $view->setData($entity->toArray());
            return $this->get('fos_rest.view_handler')->handle($view);
        } else {
            return View::create($form, 400);
        }
    }

    public function putVmsAction(VirtualMachine $entity) {
        return $this->processForm($entity);
    }

    /**
     * @Rest\View
     */
    public function getVmsAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return array();
        }


        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:VirtualMachine')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }


        return $array;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteVmsAction(VirtualMachine $entity) {
        $this->get('virtualmachine.service')->remove($entity);
    }

    /**
     * @Rest\View
     */
    public function getVmsStartAction(VirtualMachine $entity) {
        $user = $this->get('security.context')->getToken()->getUser();
        $job = new Job('zevirt:vm', array('start', $entity->getId()));
        $job->setUser($user);
        $job->setName('Start ' . $entity->getName());


        $em = $this->getDoctrine()->getManager();
        $em->persist($job);
        $em->flush($job);
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function getVmsShutdownAction(VirtualMachine $entity) {
        $user = $this->get('security.context')->getToken()->getUser();
        $job = new Job('zevirt:vm', array('shutdown', $entity->getId()));
        $job->setUser($user);
        $job->setName('Shutdown ' . $entity->getName());

        $em = $this->getDoctrine()->getManager();
        $em->persist($job);
        $em->flush($job);
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function getVmsResetAction(VirtualMachine $entity) {
        $user = $this->get('security.context')->getToken()->getUser();
        $job = new Job('zevirt:vm', array('reset', $entity->getId()));
        $job->setUser($user);
        $job->setName('Reset ' . $entity->getName());

        $em = $this->getDoctrine()->getManager();
        $em->persist($job);
        $em->flush($job);
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function getVmsDestroyAction(VirtualMachine $entity) {
        $user = $this->get('security.context')->getToken()->getUser();
        $job = new Job('zevirt:vm', array('destroy', $entity->getId()));
        $job->setUser($user);
        $job->setName('Hard Shutdown ' . $entity->getName());

        $em = $this->getDoctrine()->getManager();
        $em->persist($job);
        $em->flush($job);
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function getVmsMigratesAction(VirtualMachine $entity, Node $node) {
        $user = $this->get('security.context')->getToken()->getUser();
        $job = new Job('zevirt:vm', array('migrate', $entity->getId(), $node->getId()));
        $job->setUser($user);
        $job->setName('Migrate ' . $entity->getName() . ' to node ' . $node->getName());

        $em = $this->getDoctrine()->getManager();
        $em->persist($job);
        $em->flush($job);
    }

}
