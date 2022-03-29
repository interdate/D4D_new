<?php


namespace App\Form\Type;

use App\Entity\Maritalstatus;
use App\Entity\Sexpref;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SignUpFourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('smoking', EntityType::class, array(
            'class' => 'App:Smoking',
            'label' => "Smoking*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'required' => true,
            'empty_data' => null,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('drinking', EntityType::class, array(
            'class' => 'App:Drinking',
            'label' => "Drinking*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'required' => true,
            'empty_data' => null,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('aboutMe', TextareaType::class, array('label' => 'About Me*', 'required' => true));

        $builder->add('lookingFor', TextareaType::class, array('label' => 'Looking For*', 'required' => true));

        $builder->add('hobbies', EntityType::class, array(
            'class' => 'App:Hobby',
            'label' => "Hobbies",
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'empty_data' => null,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('characteristics', EntityType::class, array(
            'class' => 'App:Characteristic',
            'label' => "Characteristics",
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'empty_data' => null,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('lookingfors', EntityType::class, array(
            'class' => 'App:Lookingfor',
            'label' => "Looking For",
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'empty_data' => null,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('health', EntityType::class, array(
            'class' => 'App:Health',
            'label' => "Life Challenge*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('mobility', EntityType::class, array(
            'class' => 'App:Mobility',
            'label' => "Mobility*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
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
