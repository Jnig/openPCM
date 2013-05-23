<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Symfony\Component\Security\Core\SecurityContext;

class DefaultController extends Controller {

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction() {

        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                    SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        return array();
    }

    /**
     * @Route("/vnc/{entity}")
     * @Template()
     */
    public function vncAction(VirtualMachine $entity) {
        $service = $this->get('virtualmachine.service');

        $port = $service->getVncPort($entity);

        if ($port !== false) {


            $dynamicPort = mt_rand(49152, 65535);


            //TODO: install python-numpy
            //echo 'python '.APPLICATION_PATH.'/scripts/websockify '.$dynamicPort.' '.$node->host.':'.$port.' --run-once';
            //die('python '.__DIR__.'/../scripts/websockify '.$dynamicPort.' '.$entity->getNode()->getHostname().':'.$port.' --run-once --timeout=10');
            //
            exec('/usr/bin/python ' . __DIR__ . '/../scripts/websockify ' . $dynamicPort . ' ' . $entity->getNode()->getHostname() . ':' . $port . ' --run-once --timeout=10 > /dev/null 2>&1 &');


            return array('port' => $dynamicPort, 'server' => $_SERVER['SERVER_ADDR']);
        } else {
            die('not running');
        }
    }

    /**
     * @Route("/app/")
     * @Template()
     */
    public function appAction() {


        return array();
    }

}
