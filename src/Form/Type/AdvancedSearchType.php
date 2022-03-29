<?php

namespace AppBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AdvancedSearchType extends QuickSearchSidebarType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);


        $builder->add('id', TextType::class, array(
            'label' => 'מספר משתמש',
            'required' => false,

        ));

        $builder->add('username', TextType::class, array(
            'label' => 'שם משתמש',
            'required' => false,

        ));

        $builder->add('relationshipStatus', EntityType::class, array(
            'class' => 'AppBundle:RelationshipStatus',
            'label' => 'מצב משפחתי',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('children', ChoiceType::class, array(
            'label' => 'ילדים',
            'choices' => array(
                '' => null,
                'יש' => '1',
                'אין' => '2',
            ),
        ));

        $builder->add('region', EntityType::class, array(
            'class' => 'AppBundle:Region',
            'label' => 'אזור הארץ',
            'choice_label' => 'name',
            'placeholder' => 'בחר',
            'empty_data' => null,
            'multiple' => true,
            'expanded' => true
        ));

        $builder->add('gender', ChoiceType::class, array(
            'label' => 'מין',
            'choices' => array('גבר'=>1, 'זוג'=>2, 'אישה'=>3),
            'placeholder' => 'בחר',
            'empty_data'  => null,
            'multiple' => true,
            'expanded' => true,
        ));



        $choices = array();
        for($i = 18; $i <= 120; $i++){
            $choices[(string) $i] = $i;
        }

        $builder->add('ageFrom', ChoiceType::class, array(
            'label' => 'גיל',
            'choices' => $choices,
            //'data' => 18,
            'placeholder' => 'בחר',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('ageFrom1', ChoiceType::class, array(
            'label' => 'גיל',
            'choices' => $choices,
            //'data' => 18,
            'placeholder' => 'בחר',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('ageTo1', ChoiceType::class, array(
            'label' => 'גיל',
            'choices' => $choices,
            //'data' => 18,
            'placeholder' => 'בחר',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('ageTo', ChoiceType::class, array(
            'label' => 'גיל',
            'choices' => $choices,
            //'data' => 18,
            'placeholder' => 'בחר',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('city', EntityType::class, array(
            'class' => 'AppBundle:City',
            'label' => 'עיר מגורים',
            'choice_label' => 'name',
            'placeholder' => 'בחר',
            'empty_data' => null
        ));


        $choices = array();
        for ($i = 54; $i <= 96; $i++) {
            $heightStr = (int)round($i * 2.54) . " cm";
            $choices[$heightStr] = round($i * 2.54);//$heightStr;
        }


        $builder->add('heightFrom', ChoiceType::class, array(
            'choices' => $choices,
            'placeholder' => 'בחר',
            'empty_data' => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('heightTo', ChoiceType::class, array(
            'choices' => $choices,
            'placeholder' => 'בחר',
            'empty_data' => null,
            'mapped' => false,
            'required' => false,
        ));

        $choices = array();
        for ($i = 30; $i <= 200; $i++) {
            $choices[$i] = $i;//$heightStr;
        }

        $builder->add('weight', ChoiceType::class, array(
            'choices' => $choices,
            'placeholder' => 'בחר',
            'empty_data' => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('weight1', ChoiceType::class, array(
            'choices' => $choices,
            'placeholder' => 'בחר',
            'empty_data' => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('hairStyle', EntityType::class, array(
            'class' => 'AppBundle:HairStyle',
            'label' => 'תסרוקת',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('hairStyle1', EntityType::class, array(
            'class' => 'AppBundle:HairStyle',
            'label' => 'תסרוקת',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('hair', EntityType::class, array(
            'class' => 'AppBundle:Hair',
            'label' => 'צבע השער',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('hair1', EntityType::class, array(
            'class' => 'AppBundle:Hair',
            'label' => 'צבע השער',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('eyes', EntityType::class, array(
            'class' => 'AppBundle:Eyes',
            'label' => 'צבע עיניים',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('eyes1', EntityType::class, array(
            'class' => 'AppBundle:Eyes',
            'label' => 'צבע עיניים',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('body', EntityType::class, array(
            'class' => 'AppBundle:Body',
            'label' => 'מבנה גוף',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('body1', EntityType::class, array(
            'class' => 'AppBundle:Body',
            'label' => 'מבנה גוף',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('countryOfOrigin', EntityType::class, array(
            'class' => 'AppBundle:Country',
            'label' => 'ארץ לידה',
            'choice_label' => 'name',
            'placeholder' => '',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('countryOfOrigin1', EntityType::class, array(
            'class' => 'AppBundle:Country',
            'label' => 'ארץ לידה',
            'choice_label' => 'name',
            'placeholder' => '',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('ethnicity', EntityType::class, array(
            'class' => 'AppBundle:Ethnicity',
            'label' => 'מוצא',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'mapped' => false,
        ));

        $builder->add('ethnicity1', EntityType::class, array(
            'class' => 'AppBundle:Ethnicity',
            'label' => 'מוצא',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'mapped' => false,
        ));


        $choices = array();
        for ($i = 1; $i < 151; $i++) {
            $choices[$i] = $i;
        }
        /*$builder->add('distance', ChoiceType::class, array(
            'choices' => $choices,
            'placeholder' => 'בחר',
            'mapped' => false,
            'required' => false,
        ));*/
        $builder->add('sexPref', EntityType::class, array(
            'class' => 'AppBundle:SexPref',
            'label' => 'העדפה מינית',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('sexPref1', EntityType::class, array(
            'class' => 'AppBundle:SexPref',
            'label' => 'העדפה מינית',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('experience', EntityType::class, array(
            'class' => 'AppBundle:Experience',
            'label' => 'ניסיון',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('experience1', EntityType::class, array(
            'class' => 'AppBundle:Experience',
            'label' => 'ניסיון',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('smoking', EntityType::class, array(
            'class' => 'AppBundle:Smoking',
            'label' => 'הרגלי עישון',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('smoking1', EntityType::class, array(
            'class' => 'AppBundle:Smoking',
            'label' => 'הרגלי עישון',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('drinking', EntityType::class, array(
            'class' => 'AppBundle:Drinking',
            'label' => 'הרגלי שתיה',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('drinking1', EntityType::class, array(
            'class' => 'AppBundle:Drinking',
            'label' => 'הרגלי שתיה',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('meetingTime', EntityType::class, array(
            'class' => 'AppBundle:MeetingTime',
            'label' => 'שעות מפגש מועדפות',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'required' => true,
            'placeholder' => 'בחר',
            'empty_data' => null,
        ));

        $builder->add('lookingForGender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'label' => 'שמחפשים',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('withPhoto', CheckboxType::class, array(
            'label' => 'עם תמונת פרופיל (אפשרות זו זמינה רק לחברים בעלי תמונה)',
            'mapped' => false,
            'required' => false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'do_not_create_ethnicity' => false,
            'do_not_create_zodiac' => false,
        ));
    }

}
