<?php

namespace AppBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminAdvancedSearchType extends AdvancedSearchType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('region', EntityType::class, array(
            'class' => 'AppBundle:Region',
            'label' => 'אזור',
            'choice_label' => 'name',
            'empty_data'  => null,
            'multiple' => true,
            'expanded' => true,
            'mapped' => false,
        ));

/*        $builder->add('zipCode', EntityType::class, array(
            'class' => 'AppBundle:ZipCode',
            'choice_label' => 'name',
            'empty_data'  => null,
            'choice_value' => 'name',
        ));*/

        //$builder->add('zipCodeSingle', HiddenType::class, array('mapped' => false));

        $builder->add('zodiac', EntityType::class, array(
            'class' => 'AppBundle:Zodiac',
            'label' => 'מזל',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('loginFrom', EntityType::class, array(
            'class' => 'AppBundle:LoginFrom',
            'label' => 'כניסה אחרונה מ',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));


        /*Boolean Props*/

        $builder->add('isActive', ChoiceType::class, array(
            'label' => 'פעיל',
            'choices'  => array(
                'בחר' => null,
                'כן' => true,
                'לא' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));

        //isActivated - phone activate

        $builder->add('isActivated', ChoiceType::class, array(
            'label' => 'הטלפון הופעל',
            'choices'  => array(
                'בחר' => null,
                'כן' => true,
                'לא' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('isFrozen', ChoiceType::class, array(
            'label' => 'קפוא',
            'choices'  => array(
                'בחר' => null,
                'כן' => true,
                'לא' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));


        $builder->add('isPhoto', ChoiceType::class, array(
            'label' => 'עם תמונה',
            'choices'  => array(
                'בחר' => null,
                'כן' => true,
                'לא' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('isPaying', ChoiceType::class, array(
            'label' => 'תשלום',
            'choices'  => array(
                'בחר' => null,
                'כן' => true,
                'לא' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('hasPoints', ChoiceType::class, array(
            'label' => 'עם נקודות',
            'choices'  => array(
                'בחר' => null,
                'כן' => true,
                'לא' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));

        /*
        $builder->add('isPhone', ChoiceType::class, array(
            'label' => "With Phone",
            'choices'  => array(
                'Choose' => null,
                'Yes' => true,
                'No' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));*/

        /* Date Props */

        $builder->add('startSubscriptionFrom', TextType::class, array(
            'label' => 'התחל לשלם תאריך',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('startSubscriptionTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('endSubscriptionFrom', TextType::class, array(
            'label' => 'תאריך התשלום הסופי',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('endSubscriptionTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));


        $builder->add('signUpFrom', TextType::class, array(
            'label' => 'תאריך הרשמה',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('signUpTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('lastVisitedFrom', TextType::class, array(
            'label' => 'תאריך ביקור אחרון',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('lastVisitedTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));


        /* Other */

        $builder->add('ip', 'text', array(
            'label' => 'IP',
            'required' => false,
        ));

        $builder->add('gender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'label' => 'מין',
            'choice_label' => 'name',
            //'placeholder' => 'Choose',
            'empty_data'  => null,
            'multiple' => true,
            'expanded' => true,
            'mapped' => false,
        ));

        $builder->add('phone_is', ChoiceType::class, array(
            'label' => 'עם טלפון',
            'choices'  => array(
                'בחר' => null,
                'כן' => true,
                'לא' => false,
            ),
            'mapped' => false,
            'required' => false,
        ));

    }
}
