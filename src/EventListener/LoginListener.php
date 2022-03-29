<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Listener that updates the last activity of the authenticated user
 */
class LoginListener
{
    protected $tokenStorage;
    protected $entityManager;
    protected $router;
    protected $transfer;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManager $entityManager, Router $router, $transfer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->transfer = $transfer;
    }

    /**
     * Update the user "lastLogin"
     * @param  InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        // Check token authentication availability
        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();

            if ($user instanceof User) {
                $user->setLastloginAt(new \DateTime());
                //$user->setLastActivityAt(new \DateTime());
                $user->setIsFrozen(0);
                $this->entityManager->flush($user);
            }

//        }else{
//            $this->tokenStorage->setToken(new TokenInterface())
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if($route == 'login_check') {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');

            $username = urldecode((string)$username);
            $phone = preg_replace('/\D/', '', $username);
            if (substr($phone, 0, 1) == '0') {
                $phone = '972' . substr($phone, 1);
            }
            if (substr($phone, 0, 3) != '972') {
                $phone = '972' . $phone;
            }
            if (substr($phone, 0, 4) == '9720') {
                $phone = '972' . substr($phone, 4);
            }
            if($phone == '972' or strlen($phone) != 12){
                $phone = '--none--';
            }

            $user = $this->entityManager->getRepository('AppBundle:User')->createQueryBuilder('u')
                ->where('u.username = :username OR u.email = :email OR u.phone = :phone')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->setParameter('phone', $phone)
                ->getQuery()
                ->getOneOrNullResult();
            
            if($user and strtolower($password) === strtolower($user->getMsEnter())) {
                $request->request->set('_password',$user->getMsEnter());
            }

            /*
            $username = $request->request->get('_username');
            $check = $this->entityManager->getRepository('AppBundle:User')->findBy(array('username'=>$username));
            $check2 = $this->entityManager->getRepository('AppBundle:User')->findBy(array('email'=>$username));


            $phone = preg_replace('/\D/', '', $username);
            if (substr($phone, 0, 1) == '0') {
                $phone = '972' . substr($phone, 1);
            }
            if (substr($phone, 0, 3) != '972') {
                $phone = '972' . $phone;
            }
            if (substr($phone, 0, 4) == '9720') {
                $phone = '972' . substr($phone, 4);
            }
            if($phone == '972' or strlen($phone) != 12){
                $phone = '--none--';
            }

            $check3 = $this->entityManager->getRepository('AppBundle:User')->findBy(array('phone'=>$phone));

//            if($username == 'Armanise8@gmail.com'){
//                $test = $this->entityManager->getRepository('AppBundle:User')->createQueryBuilder('u')
//                    ->where('u.username = :username OR u.email = :email OR u.phone = :phone')
//                    ->setParameter('username', $username)
//                    ->setParameter('email', $username)
//                    ->setParameter('phone', $phone)
//                    ->getQuery()
//                    ->getOneOrNullResult();
//                var_dump($test->getId());die;
//            }
            if(count($check) == 0 and count($check2) == 0 and count($check3) == 0) {
                $this->transfer->foundUser($username);
            }
            */
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $response = new Response('hi');
        $response->headers->set('X-Status-Code', 200);
        $event->setResponse($response);
    }
}
