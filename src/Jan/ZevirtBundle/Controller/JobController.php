<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Form\JobType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Jan\ZevirtBundle\Entity\Job;

class JobController extends Controller {

    public function postJobsAction() {
        $entity = new Job;
        return $this->processForm($entity);
    }

    private function processForm(Job $entity) {
        $form = $this->createForm(new JobType, $entity);

        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            $view = View::create();
            return $view->setData(array('data' => $entity->getAjaxArray()));
        } else {
            return View::create($form, 400);
        }
    }

    public function putJobsAction(Job $entity) {
        return $this->processForm($entity);
    }

    /**
     * @Rest\View
     */
    public function getJobsAction($filter) {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return array();
        }
        $em = $this->getDoctrine()->getManager();


        if ($filter == 'system') {
            $query = $em->createQuery(
                            'SELECT p FROM JanZevirtBundle:Job p WHERE p.user is null ORDER BY p.createdAt DESC'
                    )->setMaxResults(100);
        } else {
            $query = $em->createQuery(
                            'SELECT p FROM JanZevirtBundle:Job p WHERE p.user is not null  ORDER BY p.createdAt DESC'
                    )->setMaxResults(100);
        }

        $entities = $query->getResult();

        $array = array();
        foreach ($entities as $entity) {

            $array[] = $entity->toArray();
        }

        return $array;
    }

    /**
     * @Rest\View
     */
    public function getJobsOutputAction(Job $job) {

        return array('output' => $job->getJob()->getOutput() . $job->getJob()->getErrorOutput(), 'state' => $job->getJob()->getState());
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteJobsAction(Job $entity) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

}
