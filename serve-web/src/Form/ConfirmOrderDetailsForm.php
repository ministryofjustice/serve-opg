<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Order;
use App\Entity\OrderPf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfirmOrderDetailsForm  extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

        if ($options['show_subType_question']) {
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
            ]);
        }

        if ($options['show_appointmentType_question']) {
            $builder->add('appointmentType', ChoiceType::class, [
                'translation_domain' => 'forms',
                'label' => 'Appointment type',
                'required' => false,
                'choices' => [
                    'common.choices.pleaseSelect' => '',
                    'order.appointmentType.choices.SOLE' => Order::APPOINTMENT_TYPE_SOLE,
                    'order.appointmentType.choices.JOINT' => Order::APPOINTMENT_TYPE_JOINT,
                    'order.appointmentType.choices.JOINT_AND_SEVERAL' => Order::APPOINTMENT_TYPE_JOINT_AND_SEVERAL,
                ]
            ]);
        }
        $builder->add('submit', SubmitType::class, ['translation_domain' => 'forms', 'label' => 'common.submit.label']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Order::class,
                'validation_groups' => function (FormInterface $form) {
                    /* @var $data Order */
                    $order = $form->getData();

                    return array_filter([
                        $order instanceof OrderPf ? 'order-has-assets' : null,
                        'order-subtype',
                        'appointment-type'
                    ]);
                }
            ]
        )
        ->setRequired(
            ['show_assets_question', 'show_subType_question' , 'show_appointmentType_question']
        );
    }
}