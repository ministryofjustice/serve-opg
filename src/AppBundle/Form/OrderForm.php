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
    const HAS_ASSETS_TRANS_PREFIX = 'order.hasAsssets.';
    const SUBTYPE_TRANS_PREFIX = 'order.subType.';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasAssetsAboveThreshold', ChoiceType::class, [
                'label' => 'Are the bond or assets above £21,000?',
                'choices' => [
                    'Please select...' => '',
                    self::HAS_ASSETS_TRANS_PREFIX . Order::HAS_ASSETS_NA => Order::HAS_ASSETS_NA,
                    self::HAS_ASSETS_TRANS_PREFIX . Order::HAS_ASSETS_YES => Order::HAS_ASSETS_YES,
                    self::HAS_ASSETS_TRANS_PREFIX . Order::HAS_ASSETS_NO => Order::HAS_ASSETS_NO,
                ]
            ])
            ->add('subType', ChoiceType::class, [
                'label' => 'Order subtype',
                'choices' => [
                    'Please select...' => '',
                    self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_NEW => Order::SUBTYPE_NEW,
                    self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_REPLACEMENT => Order::SUBTYPE_REPLACEMENT,
                    self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_INTERIM_ORDER => Order::SUBTYPE_INTERIM_ORDER,
                    self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_TRUSTEE => Order::SUBTYPE_TRUSTEE,
                    self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_VARIATION => Order::SUBTYPE_VARIATION,
                    self::SUBTYPE_TRANS_PREFIX . Order::SUBTYPE_DIRECTION => Order::SUBTYPE_DIRECTION
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save and continue']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
        ));
    }
}