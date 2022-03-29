<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class FaqCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'Name',
            'required' => true,
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'Active',
            'required' => false,
        ));
    }

    public function setDefaultOptions(ExceptionInterface $resolver)
    {
        $resolver->setDefaults(array(
            //'csrf_protection' => false,
            //'attr' => array('novalidate' => 'novalidate')
        ));
    }


    public function getName(){
        return 'settings';
    }
}
