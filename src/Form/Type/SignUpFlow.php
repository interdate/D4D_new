<?php

namespace AppBundle\Form\Type;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SignUpFlow extends FormFlow
{
    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'Step 1',
                'form_type' => 'AppBundle\Form\Type\SignUpOneType',
                'form_options' => array(
                    'validation_groups' => array('sign_up_one'),
                ),
            ),
            array(
                'label' => 'Step 2',
                'form_type' => 'AppBundle\Form\Type\SignUpTwoType',
                'form_options' => array(
                    'validation_groups' => array('sign_up_two'),
                ),
            ),
            /*
           array(
               'label' => 'Step 3',
               'form_type' => 'AppBundle\Form\Type\SignUpThreeType',
               'form_options' => array(
                   'validation_groups' => array('sign_up_three'),
               ),
           ),

           array(
               'label' => 'הוספת תמונה',
               'form_type' => new SignUpFourType(),
           ),
           array(
               'label' => 'הוספת תמונה',
               'form_type' => new SignUpFiveType(),
           ),
           */
        );
    }

    public function getFormOptions($step, array $options = array()) {
        $options = parent::getFormOptions($step, $options);

        if ($step === 2 and is_object($this->getFormData()->getGender())) {
            $options['is_male'] = $this->getFormData()->getGender()->getId();
        }
        return $options;
    }


    public function getName()
    {
        return 'signUp';
    }

}
