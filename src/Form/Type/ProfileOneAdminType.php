<?php


namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SignUpOneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, array(
            'label' => 'Username*',
            'invalid_message' => 'Invalid username',
            'required' => true,
        ));

        $builder->add('email', RepeatedType::class, array(
            'type' => TextType::class,
            'invalid_message' => "Invalid Email",
            'options'           => array(
                'attr' => array(
                    'class'     => 'email-field',
                    'message'   => '<div class="messageForm">Your Email address is kept private.
                            Please enter a valid email as your Login details will be sent to this email.
                            This is kept private and is not revealed to other members or any third party.
                            <a href="/pages/7" target="_blank">If you wish to know more about our Confidential Details press Here.</a></div>'
                )
            ),
            'required' => true,
            'first_options' => array('label' => 'Email*'),
            'second_options' => array('label' => 'Retype Email*'),
        ));

        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => "Incompatible passwords",
            'required' => true,
            'first_options' => array('label' => 'Password*'),
            'second_options' => array('label' => 'Retype password*'),
        ));


        $builder->add('gender', EntityType::class, array(
            'class' => 'App:Gender',
            'label' => "Gender*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('g')
                    ->orderBy('g.name', 'ASC');
            },
        ));

        $builder->add('isPushOnNewMess', CheckboxType::class, array(
            'required' => false,
            'label' => 'I agree to receive push notification',
        ));

        $builder->add('isGetMsgToEmail', CheckboxType::class, array(
            'required' => false,
            'label' => 'I agree to receive promotional material by email', // or SMS
        ));

        $builder->add('agree', CheckboxType::class, array(
            'mapped' => false,
            'attr' => array('id' => 'x1'),
        ));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('sign_up_one'),
        ));
    }

}
