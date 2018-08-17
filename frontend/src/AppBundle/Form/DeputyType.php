<?php

namespace AppBundle\Form;

use AppBundle\Entity\Deputy;
use AppBundle\Entity\Order;
use AppBundle\Entity\Post;
use Common\Form\Answers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeputyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderType', ChoiceType::class, [
                'choices' => [
                    'Property and affairs' => Order::TYPE_PROPERTY_AFFAIRS,
                    'Health and welfare' => Order::TYPE_HEALTH_WELFARE,
                    'Both' => Order::TYPE_BOTH,
                ]
            ])
            ->add('appointmentType', ChoiceType::class, [
                'choices' => [
                    'Sole' => Deputy::APPOINTMENT_TYPE_SOLE,
                    'Joint' => Deputy::APPOINTMENT_TYPE_JOINT,
                    'Joint and several' => Deputy::APPOINTMENT_TYPE_JOINT_AND_SEVERAL
                ]
            ])
            ->add('deputyType', ChoiceType::class, [
                'choices' => [
                    'Lay' => Deputy::DEPUTY_TYPE_LAY,
                    'Public authority' => Deputy::DEPUTY_TYPE_PA,
                    'Professional' => Deputy::DEPUTY_TYPE_PROF
                ]
            ])
            ->add('forename', TextType::class)
            ->add('surname', TextType::class)
            ->add('emailAddress', TextType::class)
            ->add('contactNumber', TextType::class)
            ->add('organisationName', TextType::class)
            ->add('addressLine1', TextType::class)
            ->add('addressLine2', TextType::class)
            ->add('addressLine3', TextType::class)
            ->add('addressTown', TextType::class)
            ->add('addressCounty', TextType::class)
            ->add('addressCountry', TextType::class)
            ->add('addressPostcode', TextType::class)
            ->add('deputyAnswerQ2_6', ChoiceType::class, [
                'choices' => [
                    Answers::ANSWER_YES => Answers::ANSWERED_YES,
                    Answers::ANSWER_NO => Answers::ANSWERED_NO
                ]
            ])
            ->add('deputyS4Response', ChoiceType::class, [
                'choices' => [
                    Answers::ANSWER_YES => Answers::ANSWER_YES,
                    Answers::ANSWER_NO => Answers::ANSWER_NO
                ]
            ])
            ->add('saveAndAddAnother', SubmitType::class)
            ->add('saveAndContinue', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Deputy::class,
        ));
    }
}
