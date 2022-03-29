<?php

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\Settings;
use AppBundle\Entity\Coupon;
use AppBundle\Form\Type\SettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends Controller
{
    /**
     * @Route("/admin/settings", name="admin_settings")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $settings = $em->getRepository('AppBundle:Settings')->find(1);
        $coupons = $em->getRepository('AppBundle:Coupon')->findAll();

        $form = $this->createForm(SettingsType::class, $settings);

        if($request->isMethod('Post')){
            $form->handleRequest($request);
            if($form->isValid() && $form->isSubmitted()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($settings);
                $em->flush();
            }
        }

        return $this->render('backend/settings/index.html.twig', array(
            'form' => $form->createView(),
            'coupons' => $coupons
        ));
    }




}
