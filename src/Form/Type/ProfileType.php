<?php


namespace App\Form\Type;

use App\Entity\LocCountries;
use App\Entity\LocRegions;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class SignUpTwoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('birthday', DateType::class, array(
            'label' => 'Birthday*',
            'widget' => 'choice',
            'years' => range(date('Y') - 18, date('Y') - 120),
            'placeholder' => array('year' => 'year', 'month' => 'month', 'day' => 'day'),
            'empty_data' => null,
        ));

        $builder->add('country', EntityType::class, array(
            'class' => 'App:LocCountries',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.orderId', 'DESC')->addOrderBy('c.name', 'ASC');
            },
            'label' => 'Country*',
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
        ));

        $builder->add('region', EntityType::class, array(
            'class' => 'App:LocRegions',
            'label' => 'State*',
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null,
        ));

        $builder->add('city', EntityType::class, array(
            'class' => 'App:LocCities',
            'label' => 'City*',
            'choice_label' => 'name',
            'placeholder' => 'Choose',
            'empty_data' => null
        ));

        $addRegions = function (FormInterface $form, LocCountries $country = null) {

            $regions = null === $country ? array() : $country->getRegions();

            $form->add('region', EntityType::class, array(
                'class' => 'App:LocRegions',
                'label' => 'State*',
                'choice_label' => 'name',
                'placeholder' => 'Choose',
                'choices'     => $regions,
                'empty_data'  => null,
            ));
        };

        $addCities = function (FormInterface $form, LocRegions $region = null) {

            $cities = null === $region ? array() : $region->getCities();

            $form->add('city', EntityType::class, array(
                'class'       => 'App:LocCities',
                'label' => 'City*',
                'choice_label' => 'name',
                'placeholder' => 'Choose',
                'choices'     => $cities,
                'empty_data'  => null,
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($addCities, $addRegions) {
                $data = $event->getData();
                $form = $event->getForm();
                $addRegions($form, $data->getRegion());
                $addCities($form, $data->getCity());
            }
        );


        $builder->get('country')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($addRegions) {
                $country = $event->getForm()->getData();
                $addRegions($event->getForm()->getParent(), $country);
            }
        );

        $builder->get('region')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($addCities) {
                $region = $event->getForm()->getData();
                $addCities($event->getForm()->getParent(), $region);
            }
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use($addCities, $addRegions){
                $data = $event->getData();
                $form = $event->getForm();
                $addRegions($form, $data->getCountry());
                $addCities( $form, $data->getRegion());
            }
        );

//        $builder->add('isSentEmail', CheckboxType::class, array(
//            'required' => false,
//            'label' => 'I agree to receive promotional material by email', // or SMS
//        ));
//
//        $builder->add('isSentPush', CheckboxType::class, array(
//            'required' => false,
//            'label' => 'I agree to receive push notification',
//        ));
        $builder->add('zipCode', TextType::class, array(
            'label' => 'Zip Code*',
            'invalid_message' => 'Invalid first name',
            'required' => true,
        ));

        $builder->add('firstName', TextType::class, array(
            'label' => 'First Name*',
            'invalid_message' => 'Invalid first name',
            'required' => true,
        ));

        $builder->add('lastName', TextType::class, array(
            'label' => 'Last Name*',
            'invalid_message' => 'Invalid last name',
            'required' => true,
        ));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('sign_up_two'),
        ));
    }

}
