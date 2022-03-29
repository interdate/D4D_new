<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class QuickSearchType extends QuickSearchSidebarType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('username', TextType::class, array(
            'label' => 'חפש לפי שם כינוי:',
            'required' => false,
        ));

        $builder->add('gender', ChoiceType::class, array(
            'label' => 'מין',
            'choices' => array('גבר'=>1, 'זוג'=>2, 'אישה'=>3),
            //'data' => 18,

            'placeholder' => 'בחר',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('lookingForGender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'label' => 'שמחפשים',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

    }


}
