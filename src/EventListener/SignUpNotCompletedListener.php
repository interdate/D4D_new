<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Listener that prompts male activate account if not activated
 */
class SignUpNotCompletedListener
{
    protected $tokenStorage;
    protected $entityManager;
    protected $router;
    protected $container;
    protected $targetRoutes;
    protected $excludedRoutes;

    public function __construct(TokenStorage $tokenStorage, Router $router, EntityManager $entityManager, $targetRoutes, $excludedRoutes)
    {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->targetRoutes = $targetRoutes;
        $this->entityManager = $entityManager;
        $this->excludedRoutes = $excludedRoutes;
//        $this->excludedRoutes[] = $this->targetRoutes['male'];
//        $this->excludedRoutes[] = $this->targetRoutes['female'];
    }

    /**
     * Redirects a male user to Activation page if not activated
     * @param FilterControllerEvent $event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        //var_dump($request);die;
        // Check that the current request is a "MASTER_REQUEST"
        // Or that the current request is not a target request
        // Ignore any sub-request

        // $this->excludedRoutes[] = 'transfer_user_data';


        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST ||
            in_array($event->getRequest()->get('_route'), $this->excludedRoutes) ||
            !$this->tokenStorage->getToken() ||
            strpos($event->getRequest()->get('_route'), 'api_1_') !== false || strpos($event->getRequest()->get('_route'), 'api_2_') !== false
        )
        {
            return;
        }
        // var_dump($event->getRequest()->get('_route'), $this->excludedRoutes);die;
        // var_dump(strpos($event->getRequest()->get('_route'), 'api_2_') !== false);die;
        $user = $this->tokenStorage->getToken()->getUser();
        //var_dump($this->excludedRoutes,in_array($event->getRequest()->get('_route'), $this->excludedRoutes), $event->getRequest()->get('_route'));die;
        if ($user instanceof User) {
            //if(!in_array($route, array('sign_up_activation','payment_subscribe','contact'))) {
                if (!$user->getIsActivated()) {
                    $status = 'not_activated';
                    $redirectUrl = $this->router->generate('sign_up_activation');
                    //return;
                } elseif ((!$user->getRegion() || !$user->getCity()) and !($user->getGender()->getId() == 1 and !$user->is2D() and !$user->isPaying()
                        and ($this->entityManager->getRepository('AppBundle:Settings')->find(1)->getIsCharge()))) {
                    $session = new Session();
                    $session->set('has_region', 1);

                    $session->get('has_region');

                    $redirectUrl = $this->router->generate('user_profile', array('empty_region' => 'true'));

                } elseif ($user->getId() != 3 and $user->getGender()->getId() == 1 and !$user->is2D() and !$user->isPaying()
                    and ($this->entityManager->getRepository('AppBundle:Settings')->find(1)->getIsCharge())) // or $user->getId() == 3
                {
                    //var_dump($user->getGender()->getId() == 1, $user->is2D(),$user->isPaying());die;
                    if(!in_array($event->getRequest()->get('_route'), array('sign_up_subscription','payment_subscribe','contact'))) {
                        $redirectUrl = $this->router->generate('sign_up_subscription');
                    }else{
                        return;
                    }
                }
            //}
            else{
                return;
            }





            $event->setController(function() use ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            });
        }

    }

    public function onKernelResponse(FilterResponseEvent $event)
    {


            if(strpos($event->getRequest()->get('_route'), 'api_1_') !== false || strpos($event->getRequest()->get('_route'), 'api_2_') !== false){
                $status = $event->getRequest()->attributes->get('status');
                //$event->setResponse();
                //$event->getRequest()->attributes->set('status', $status);
                //return;// new Response(json_encode(array('status' => $status)));
                $event->setResponse(new Response(json_encode(array('status' => $status))));
                //new Response(json_encode(array('status' => $status)));
                //$event->getRequest()->attributes->set('auth_token', $token);


            }
            return;

    }
}
