<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class ContactType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class, array(
            'label' => '* E-mail',
            'description' => 'User E-mail',
            'constraints' => array(
                new Constraints\NotBlank(),
                new Constraints\Email(array(
                    'checkMX' => true,
                )),
            ),

        ));

        $builder->add('subject', TextType::class, array(
            'label' => '* Subject',
            'mapped' => false,
            'description' => 'Subject',
            'constraints' => array(
                new Constraints\NotBlank(),
                new Constraints\Length(array('min' => 3)),
            ),
        ));

        $builder->add('text', TextareaType::class, array(
            'label' => '* Message',
            'mapped' => false,
            'description' => 'Message',
            'constraints' => array(
                new Constraints\NotBlank(),
                new Constraints\Length(array('min' => 3)),
            ),
        ));
    }

    public function getName()
    {
        return 'contact';
    }

}