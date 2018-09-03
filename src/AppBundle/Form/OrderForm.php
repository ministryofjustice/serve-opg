<?php

namespace AppBundle\Form;

use AppBundle\Entity\Order;
use AppBundle\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OrderForm extends AbstractType
{
    const HAS_ASSETS_TRANS_PREFIX = 'order.hasAssets.';
    const SUBTYPE_TRANS_PREFIX = 'order.subType.';
    const APPOINTMENT_TYPE_TRANS_PREFIX = 'order.appointmentType.';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['show_assets_question']) {
            $builder
                ->add('hasAssetsAboveThreshold', ChoiceType::class, [
                    'label' => 'Are the bond or assets above £21,000?',
                    'required' => false,
                    'choices' => [
                        'pleaseSelect' => '',
                        self::HAS_ASSETS_TRANS_PREFIX . Order::HAS_ASSETS_NA => Order::HAS_ASSETS_NA,
                        self::HAS_ASSETS_TRANS_PREFIX . Order::HAS_ASSETS_YES => Order::HAS_ASSETS_YES,
                        self::HAS_ASSETS_TRANS_PREFIX . Order::HAS_ASSETS_NO => Order::HAS_ASSETS_NO,
                    ]
                ]);
        }
        $builder->add('subType', ChoiceType::class, [
            'label' => 'Order subtype',
            'required' => false,
            'choices' => [
                'pleaseSelect' => '',
                self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_NEW => Order::SUBTYPE_NEW,
                self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_REPLACEMENT => Order::SUBTYPE_REPLACEMENT,
                self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_INTERIM_ORDER => Order::SUBTYPE_INTERIM_ORDER,
                self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_TRUSTEE => Order::SUBTYPE_TRUSTEE,
                self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_VARIATION => Order::SUBTYPE_VARIATION,
                self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_DIRECTION => Order::SUBTYPE_DIRECTION
            ]
        ])
            ->add('appointmentType', ChoiceType::class, [
                'label' => 'Appointment type',
                'required' => false,
                'choices' => [
                    'pleaseSelect' => '',
                    self::APPOINTMENT_TYPE_TRANS_PREFIX . Order::APPOINTMENT_TYPE_SOLE => Order::APPOINTMENT_TYPE_SOLE,
                    self::APPOINTMENT_TYPE_TRANS_PREFIX . Order::APPOINTMENT_TYPE_JOINT => Order::APPOINTMENT_TYPE_JOINT,
                    self::APPOINTMENT_TYPE_TRANS_PREFIX . Order::APPOINTMENT_TYPE_JOINT_AND_SEVERAL => Order::APPOINTMENT_TYPE_JOINT_AND_SEVERAL,
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save and continue']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
            'show_assets_question' => true,
        ));
    }
}