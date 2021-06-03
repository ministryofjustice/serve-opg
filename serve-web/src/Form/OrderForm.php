<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\OrderPf;
use App\Entity\Post;
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
                    'translation_domain' => 'forms',
                    'label' => 'order.hasAssetsAboveThreshold.label',
                    'required' => false,
                    'choices' => [
                        'common.choices.pleaseSelect' => '',
                        'common.choices.yes' => Order::HAS_ASSETS_ABOVE_THRESHOLD_YES,
                        'common.choices.no' => Order::HAS_ASSETS_ABOVE_THRESHOLD_NO,
                    ]
                ]);
        }
        $builder->add('subType', ChoiceType::class, [
            'translation_domain' => 'forms',
            'label' => 'order.subType.label',
            'required' => false,
            'choices' => [
                'common.choices.pleaseSelect' => '',
                'order.subType.choices.NEW_APPLICATION' => Order::SUBTYPE_NEW,
                'order.subType.choices.REPLACEMENT_OF_DISCHARGED_DEPUTY' => Order::SUBTYPE_REPLACEMENT,
                'order.subType.choices.INTERIM_ORDER' => Order::SUBTYPE_INTERIM_ORDER
            ]
        ])
            ->add('appointmentType', ChoiceType::class, [
                'translation_domain' => 'forms',
                'label' => 'Appointment type',
                'required' => false,
                'choices' => [
                    'common.choices.pleaseSelect' => '',
                    'order.appointmentType.choices.SOLE' => Order::APPOINTMENT_TYPE_SOLE,
                    'order.appointmentType.choices.JOINT' => Order::APPOINTMENT_TYPE_JOINT,
                    'order.appointmentType.choices.JOINT_AND_SEVERAL' => Order::APPOINTMENT_TYPE_JOINT_AND_SEVERAL,
                ]
            ])
            ->add('submit', SubmitType::class, ['translation_domain' => 'forms', 'label' => 'common.submit.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
            'show_assets_question' => true,
            'validation_groups' => function (FormInterface $form) {
                /* @var $data Order */
                $order = $form->getData();

                return array_filter([
                    $order instanceof OrderPf ? 'order-has-assets' : null,
                    'order-subtype',
                    'appointment-type'
                ]);
            }
        ));
    }
}
