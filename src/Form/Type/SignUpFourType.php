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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SignUpThreeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sexPref', EntityType::class, array(
            'class' => 'App:Sexpref',
            'label' => "Sexual Preference*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'required' => true,
            'empty_data' => null,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('maritalStatus', EntityType::class, array(
            'class' => 'App:Maritalstatus',
            'label' => "Marital Status*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'required' => true,
            'empty_data' => null,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $userChildrenList = [];//array('--' => 'Choose' );
        $userChildrenList['None'] = 0;

        for($i = 1; $i <= 10; $i++){
            $userChildrenList[$i] = $i;
        }
        $builder->add('children', ChoiceType::class, array(
            'label' => 'Children*',
            'placeholder' => 'Choose',
            'attr' => array('required' => 'required'),
            'choices' => $userChildrenList,
            'required' => true,
        ));

        $builder->add('ethnicOrigin', EntityType::class, array(
            'class' => 'App:Ethnicorigin',
            'label' => "Ethnicity*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('religion', EntityType::class, array(
            'class' => 'App:Religion',
            'label' => "Ethnicity*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('education', EntityType::class, array(
            'class' => 'App:Education',
            'label' => "Education*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('occupation', EntityType::class, array(
            'class' => 'App:Occupation',
            'label' => "Occupation*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('income', EntityType::class, array(
            'class' => 'App:Income',
            'label' => "Income*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('languages', EntityType::class, array(
            'class' => 'App:Language',
            'label' => "Language*",
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('appearance', EntityType::class, array(
            'class' => 'App:Appearance',
            'label' => "Appearance",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('bodyType', EntityType::class, array(
            'class' => 'App:Bodytype',
            'label' => "Body Style*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $heightList = [];//array('--' => 'Choose' );
        //$userChildrenList['None'] = 0;

        for($i = 54; $i <= 96; $i++){
            $index = (int) ($i / 12) . "' " . ($i % 12) . "\" (" . round($i * 2.54) . " cm)";
            $heightList[$index] = $i;
        }
        $builder->add('height', ChoiceType::class, array(
            'label' => 'Height*',
            'placeholder' => 'Choose',
            'attr' => array('required' => 'required'),
            'choices' => $heightList,
            'required' => true,
        ));

        $weightList = [];//array('--' => 'Choose' );
        //$userChildrenList['None'] = 0;

        for($i = 54; $i <= 96; $i++){
            $index = (int) ($i / 12) . "' " . ($i % 12) . "\" (" . round($i * 2.54) . " cm)";
            $weightList[$index] = $i;
        }
        $builder->add('weight', ChoiceType::class, array(
            'label' => 'Weight',
            'placeholder' => 'Choose',
            'attr' => array('required' => 'required'),
            'choices' => $weightList,
            'required' => false,
        ));

        $builder->add('hairLength', EntityType::class, array(
            'class' => 'App:Hairlength',
            'label' => "Hair style*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('hairColor', EntityType::class, array(
            'class' => 'App:Haircolor',
            'label' => "Hair color*",
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.id', 'ASC');
            },
        ));

        $builder->add('eyesColor', EntityType::class, array(
            'class' => 'App:Eyescolor',
            'label' => "Eyes color*",
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
