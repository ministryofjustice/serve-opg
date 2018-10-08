<?php

namespace AppBundle\Form;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPf;
use AppBundle\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['show_assets_question']) {
            $builder
                ->add('hasAssetsAboveThreshold', ChoiceType::class, [
                    'label' => 'Are the bond or assets above £21,000?',
                    'required' => false,
                    'choices' => [
                        'pleaseSelect' => '',
                        'order.hasAssets.yes'  => Order::HAS_ASSETS_YES,
                        'order.hasAssets.no' => Order::HAS_ASSETS_NO,
                    ]
                ]);
        }
        $builder->add('subType', ChoiceType::class, [
            'label' => 'Order subtype',
            'required' => false,
            'choices' => [
                'pleaseSelect' => '',
                'order.subType.DIRECTION' => Order::SUBTYPE_DIRECTION,
                'order.subType.INTERIM_ORDER' => Order::SUBTYPE_INTERIM_ORDER,
                'order.subType.NEW_APPLICATION' => Order::SUBTYPE_NEW,
                'order.subType.REPLACEMENT_OF_DISCHARGED_DEPUTY' => Order::SUBTYPE_REPLACEMENT,
                'order.subType.TRUSTEE' => Order::SUBTYPE_TRUSTEE,
                'order.subType.VARIATION' => Order::SUBTYPE_VARIATION,
            ]
        ])
            ->add('appointmentType', ChoiceType::class, [
                'label' => 'Appointment type',
                'required' => false,
                'choices' => [
                    'pleaseSelect' => '',
                    'order.appointmentType.SOLE' => Order::APPOINTMENT_TYPE_SOLE,
                    'order.appointmentType.JOINT' => Order::APPOINTMENT_TYPE_JOINT,
                    'order.appointmentType.JOINT_AND_SEVERAL' => Order::APPOINTMENT_TYPE_JOINT_AND_SEVERAL,
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save and continue']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
            'show_assets_question' => true,
            'validation_groups' => function (FormInterface $form) {
                /* @var $data \AppBundle\Entity\Order */

                return array_filter([
                    $form->getData() instanceof OrderPf ? 'order-has-assets' : null,
                    'order-subtype',
                    'appointment-type'
                ]);
            }
        ));
    }
}
