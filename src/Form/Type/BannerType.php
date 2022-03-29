<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->add('position', 'choice', array(
//            'label' => 'position',
////            'choice_label' => 'בחרו',
//            'placeholder' => 'בחרו',
//            'multiple' => false,
//            'expanded' => false,
//            'required' => true,
//            'choices'  => array(
//                'home_page' => 'אמוד ראשי לפני לוגין',
//                'home_page_2' => 'אמוד ראשי אחרי לוגין',
//                'profile_top' => 'אמוד פרופיל מעל תמונה',
//                'profile_bottom' => 'אמוד פרופיל מתחת תמולה',
//                'subscription_page' => 'אמוד רכישת מנוי'
//            ),
//        ));

        $builder->add('name', 'text', array(
            'label' => 'שם',
            'required' => true,
        ));


        $builder->add('img', 'file', array(
            'label' => 'תמונה',
            'data_class' => null,
            'required' => false,
        ));

        $builder->add('href', 'text', array(
            'label' => 'לינק',
            'required' => true,
        ));

        $builder->add('isActive', 'checkbox', array(
            'label' => 'פעיל',
            'required' => false,
        ));

        $builder->add('beforeLogin', 'checkbox', array(
            'label' => 'לפני לוגין',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('afterLogin', 'checkbox', array(
            'label' => 'אחרי לוגין',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('subscriptionPage', 'checkbox', array(
            'label' => 'רכישת מנוי',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('profileBottom', 'checkbox', array(
            'label' => 'פרופיל - מתחת לתמונה',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('profileTop', 'checkbox', array(
            'label' => 'פרופיל - מעל התמונה',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('mobileApp', 'checkbox', array(
            'label' => 'מוביל ואפליקציה',
            'required' => false,
//            'empty_data' => true,
        ));

//        $builder->add('uri', 'text', array(
//            'label' => 'URI',
//            'required' => false,
//        ));
//
//        $builder->add('title', 'text', array(
//            'label' => 'Title',
//        ));
//
//        $builder->add('description', 'textarea', array(
//            'label' => 'Meta Description',
//            'required' => false,
//        ));
//
//        $builder->add('isActive', 'checkbox', array(
//            'label' => 'פעיל',
//            'required' => false,
//        ));
//
//        $builder->add('footerHeader', 'entity', array(
//            'class' => 'AppBundle:FooterHeader',
//            'label' => 'מופיע בפוטר תחת כותרת',
//            'choice_label' => 'name',
//            'placeholder' => 'בחרו',
//            'multiple' => false,
//            'expanded' => false,
//            'required' => false,
//        ));


    }
    public function getName()
    {
        return 'banner';
    }

}
