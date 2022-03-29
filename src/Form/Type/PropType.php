<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class AdminPropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

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
