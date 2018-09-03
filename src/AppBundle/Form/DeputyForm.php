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

class DeputyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deputyType', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'pleaseSelect' => '',
                    'deputy.type.lay' => Deputy::DEPUTY_TYPE_LAY,
                    'deputy.type.pa' => Deputy::DEPUTY_TYPE_PA,
                    'deputy.type.prof' => Deputy::DEPUTY_TYPE_PROF
                ]
            ])
            ->add('organisationName', TextType::class, [
                'required' => false,
            ])
            ->add('forename', TextType::class, [
                'label' => 'First name',
                'required' => false
            ])
            ->add('surname', TextType::class, [
                'label' => 'Last name',
                'required' => false
            ])
            ->add('emailAddress', TextType::class, [
                'required' => false,
            ])
            ->add('contactNumber', TextType::class, [
                'required' => false,
            ])
            ->add('addressLine1', TextType::class, [
                'required' => false,
            ])
            ->add('addressLine2', TextType::class, [
                'required' => false,
            ])
            ->add('addressLine3', TextType::class, [
                'required' => false,
            ])
            ->add('addressTown', TextType::class, [
                'required' => false,
            ])
            ->add('addressCounty', TextType::class, [
                'required' => false,
            ])
//            ->add('addressCountry', TextType::class)
            ->add('addressPostcode', TextType::class, [
                'required' => false,
            ])
            ->add('saveAndContinue', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Deputy::class,
        ));
    }
}
