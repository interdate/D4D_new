<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ApiAuthenticationListener
{

    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if(strpos($request->get('_route'), 'api_1_') !== false || strpos($request->get('_route'), 'api_2_') !== false){
            if ($exception instanceof AccessDeniedException || $exception instanceof AuthenticationCredentialsNotFoundException) {
                $responseData = array('status' => 403, 'msg' => 'User Not Authenticated');
                $response = new JsonResponse();
                $response->setData($responseData);
                $response->setStatusCode($responseData['status']);
                //$response = new Response('Forbiddens');
                //$response->headers->set('X-Status-Code', 403);
                $event->setResponse($response);

            }
        }
    }
}
