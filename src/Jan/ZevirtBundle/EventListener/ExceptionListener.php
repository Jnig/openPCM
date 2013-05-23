<?php

namespace Jan\ZevirtBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Jan\ZevirtBundle\Service\VirtualmachineService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener {

    public function onKernelException(GetResponseForExceptionEvent $event) {

        $exception = $event->getException();
        $request = $event->getRequest();


        if ($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException) {
            $event->setResponse(new Response('', 403));
        }




        if (get_class($exception) == 'Jan\ZevirtBundle\Model\ZevirtException') {
            $response = new Response();

            $json = json_encode(array('error' => $exception->getMessage()));
            $response->setContent($json);

            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode(200);
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(200);
            }

            $event->setResponse($response);
        }
    }

}
