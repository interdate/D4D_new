<?php

namespace AppBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Listener that updates the last activity of the authenticated user
 */
class ActivityListener
{
    protected $tokenStorage;
    protected $entityManager;

    public function __construct(TokenStorage $tokenStorage, EntityManager $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * Update the user "lastActivity" on each request
     * @param FilterControllerEvent $event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        // Check that the current request is a "MASTER_REQUEST"
        // Ignore any sub-request
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        // Check token authentication availability
        if ($this->tokenStorage->getToken()) {

            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {

                $user->setLastRealActivityAt(new \DateTime());

                if (!$user->isOnline()) {
                    $user->setLastActivityAt(new \DateTime());
                }

//                if (!$user->is2D() && !$user->isPaying() && $user->getGender()->getId() == 1) {
//
//
//                    //$regex = '/(user\/subscription)|(sign_up\/subscription)|(contact)|(faq)|(blog)/';
//                    $regex = '/(user\/subscription)|(sign_up\/subscription)|(contact)|(faq)|(blog)/';
//
//
//                   // echo preg_match($regex, $event->getRequest()->getUri());
//
//
//                    if (!preg_match($regex, $event->getRequest()->getUri()) and strpos($event->getRequest()->get('_route'), 'api_2_') === false) {
//
//
//                       $event->setController(function () {
//                            return new RedirectResponse("/user/subscription");
//                        });
//                    }else{
//
//
//
//                    }
//                }

                $this->entityManager->flush($user);
            }
        }
    }
}
