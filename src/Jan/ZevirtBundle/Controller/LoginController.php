<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Symfony\Component\Security\Core\SecurityContext;

class LoginController extends Controller {

    /**
     * @Route("/profile")
     * @Template()
     */
    public function statusAction() {
        $user = $this->get('security.context')->getToken()->getUser();

        die(json_encode($user->toArray()));
    }

    /**
     * @Route("/login_failed")
     * @Template()
     */
    public function loginFailedAction() {

        $result = array();
        $result["success"] = false;
        $result["errors"]["reason"] = 'Bad credentials';

        die(json_encode($result));
    }

    /**
     * @Route("/login_ok")
     * @Template()
     */
    public function loginOkAction() {
        die();
    }

}
