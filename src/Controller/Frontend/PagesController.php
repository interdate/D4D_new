<?php

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\Page;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PagesController extends Controller
{
    /**
     * @Route("pages/{uri}", name="pages_page")
     */
    public function indexAction(Request $request, $uri)
    {
        $device = $this->get('mobile_detect.mobile_detector');
        return $this->render($device->isMobile() ? 'frontend/pages/index-mobile.html.twig' : 'frontend/pages/index.html.twig', array(
            'page' => $this->getDoctrine()->getManager()->getRepository('AppBundle:Page')->findOneByUri($uri),
        ));
    }

    /**
     * @Route("faq", name="faq")
     */
    public function faqAction()
    {
        $device = $this->get('mobile_detect.mobile_detector');
        return $this->render($device->isMobile() ? 'frontend/pages/faq-mobile.html.twig' : 'frontend/pages/faq.html.twig', array(
            'categories' => $this->getDoctrine()->getManager()->getRepository('AppBundle:FaqCategory')->findByIsActive(true),
            'seo' => $this->getDoctrine()->getRepository('AppBundle:Seo')->findOneByPage('faq'),
        ));
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        $device = $this->get('mobile_detect.mobile_detector');
        $sent = false;

        $user = $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')
            ? $this->getUser()
            : new User();

        $form = $this->createForm(ContactType::class);

        if ($request->isMethod('Post')) {

            $form->handleRequest($request);

            if ($form->isValid() && $form->isSubmitted()) {

                $email = ($form->get('email')->getData()) ? $form->get('email')->getData() : $user->getEmail();

                $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

                $subject = "זיגזוג | צור קשר | " . $form->get('subject')->getData();
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: ' . $email . ' <' . $email . '>' . "\r\n";

                $text = '<div dir="rtl">';
                $text .= $form->get('text')->getData() . "\r\n";
                $text .= $user->getId() ? 'מאת משתמש: (' . $user->getUsername() . '  ' . $user->getId() . ')' .  "\r\n" : '';
                $text .= "נשלח מ: Desktop";
                $text .= '</div>';

               // mail('pavel@interdate-ltd.co.il', $subject, nl2br($text), $headers/*,'-f'.$email*/);
                mail('pavel@interdate-ltd.co.il,' . $settings->getContactEmail(),$subject,$text,$headers);
                //mail('pavel@interdate-ltd.co.il',$subject,$text,$headers);

                $sent = true;
            }
        }

        return $this->render($device->isMobile() ? 'frontend/pages/contact-mobile.html.twig' : 'frontend/pages/contact.html.twig', array(
            'form' => $form->createView(),
            'sent' => $sent,
        ));
    }
}
