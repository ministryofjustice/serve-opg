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
            ->add('organisationName', TextType::class)
            ->add('deputyType', ChoiceType::class, [
                'choices' => [
                    'Please select...' => '',
                    'deputy.type.lay' => Deputy::DEPUTY_TYPE_LAY,
                    'deputy.type.pa' => Deputy::DEPUTY_TYPE_PA,
                    'deputy.type.prof' => Deputy::DEPUTY_TYPE_PROF
                ]
            ])
            ->add('forename', TextType::class, ['label'=>'First name'])
            ->add('surname', TextType::class, ['label'=>'Last name'])
            ->add('emailAddress', TextType::class)
            ->add('contactNumber', TextType::class)
            ->add('addressLine1', TextType::class)
            ->add('addressLine2', TextType::class)
            ->add('addressLine3', TextType::class)
            ->add('addressTown', TextType::class)
            ->add('addressCounty', TextType::class)
//            ->add('addressCountry', TextType::class)
            ->add('addressPostcode', TextType::class)
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
