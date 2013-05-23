<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\User;
use Jan\ZevirtBundle\Form\UserType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserController extends Controller {

    public function postUsersAction() {
        $entity = new User;
        return $this->processForm($entity);
    }

    private function processForm(User $entity) {
        $form = $this->createForm(new UserType, $entity);

        $form->bind($this->getRequest());
        if ($form->isValid()) {


            $entity->setLdap($this->getRequest()->request->get('ldap'));

            if ($entity->getLdap()) {
                $entity->setPassword('');
            } else {
                $password = $this->getRequest()->request->get('password');
                if ($password != '') {
                    $entity->setPlainPassword($password);
                }
            }

            $entity->setEnabled(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();



            $view = View::create();
            return $view->setData($entity->toArray());
        } else {
            return View::create($form, 400);
        }
    }

    public function putUsersAction(User $entity) {
        return $this->processForm($entity);
    }

    /**
     * @Rest\View
     */
    public function getUsersAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:User')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

    /**
     * @Rest\View(statusCode=204)
     */
    public function deleteUsersAction(User $entity) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

}
