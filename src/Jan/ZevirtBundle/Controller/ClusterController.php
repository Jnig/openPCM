<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\Cluster;
use Jan\ZevirtBundle\Form\ClusterType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class ClusterController extends Controller {

    public function postClustersAction() {
        $entity = new Cluster;
        return $this->processForm($entity);
    }

    private function processForm(Cluster $entity) {
        $form = $this->createForm(new ClusterType, $entity);

        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();


            $entity->setHa($this->getRequest()->request->get('ha'));

            $this->get('cluster.service')->persist($entity);
            $view = View::create();
            return $view->setData($entity->toArray());
        } else {
            return View::create($form, 400);
        }
    }

    public function putClustersAction(Cluster $entity) {
        return $this->processForm($entity);
    }

    /**
     * @Rest\View
     */
    public function getClustersAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Cluster')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteClustersAction(Cluster $entity) {

        $this->get('cluster.service')->remove($entity);
    }

}
