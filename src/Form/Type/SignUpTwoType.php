<?php


namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
            'label' => '* Username',
            'invalid_message' => 'Invalid username',
            'required' => true,
        ));

        $builder->add('email', RepeatedType::class, array(
            'type' => TextType::class,
            'invalid_message' => "Invalid Email",
            'required' => true,
            'first_options' => array('label' => '* Email'),
            'second_options' => array('label' => '* Retype Email'),
        ));

        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => "Incompatible passwords",
            'required' => true,
            'first_options' => array('label' => ' * Password'),
            'second_options' => array('label' => '* Retype password'),
        ));


        $builder->add('gender', EntityType::class, array(
            'class' => 'App:Gender',
            'label' => "* Gender",
            'choice_label' => 'name',
            'placeholder' => 'בחר',
            'empty_data' => null,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('g')
                    ->orderBy('g.name', 'ASC');
            },
        ));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('sign_up_one'),
        ));
    }

}
