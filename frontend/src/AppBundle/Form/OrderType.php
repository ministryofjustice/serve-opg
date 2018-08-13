<?php

namespace AppBundle\Form;

use AppBundle\Entity\Order;
use AppBundle\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    Order::TYPE_PROPERTY_AFFAIRS => Order::TYPE_PROPERTY_AFFAIRS,
                    Order::TYPE_HEALTH_WELFARE => Order::TYPE_HEALTH_WELFARE,
                    Order::TYPE_BOTH => Order::TYPE_BOTH,
                ]
            ])
            ->add('subType', ChoiceType::class, [
                'choices' => [
                    Order::SUBTYPE_NEW => Order::SUBTYPE_NEW,
                    Order::SUBTYPE_REPLACEMENT => Order::SUBTYPE_REPLACEMENT,
                    Order::SUBTYPE_INTERIM_ORDER => Order::SUBTYPE_INTERIM_ORDER,
                    Order::SUBTYPE_TRUSTEE => Order::SUBTYPE_TRUSTEE,
                    Order::SUBTYPE_VARIATION => Order::SUBTYPE_VARIATION,
                    Order::SUBTYPE_DIRECTION => Order::SUBTYPE_DIRECTION
                ]
            ])
            ->add('hasAssetsAboveThreshold', ChoiceType::class, [
                'choices' => [
                    Order::HAS_ASSETS_YES => Order::HAS_ASSETS_YES,
                    Order::HAS_ASSETS_NO => Order::HAS_ASSETS_NO,
                    Order::HAS_ASSETS_NA => Order::HAS_ASSETS_NA,
                ]
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
        ));
    }
}