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
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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
            ->add('forename', TextType::class, [
                'label' => 'First name',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('surname', TextType::class, [
                'label' => 'Last name',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('dateOfBirth', BirthdayType::class, [
                'label' => 'Date of birth',
                'required' => false,
                'widget' => 'text',
                'placeholder' => array(
                    'day' => 'Day','month' => 'Month' , 'year' => 'Year'
                ),
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Please enter a valid date of birth'
            ])
            ->add('emailAddress', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('daytimeContactNumber', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('eveningContactNumber', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('mobileContactNumber', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressLine1', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressLine2', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressLine3', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressTown', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressCounty', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
//            ->add('addressCountry', TextType::class)
            ->add('addressPostcode', TextType::class, [
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('saveAndContinue', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Deputy::class,
            'validation_groups' => function (FormInterface $form) {

                /* @var $data \AppBundle\Entity\Deputy */
                $data = $form->getData();
                $validationGroups = ['order-deputy'];

                if (in_array($data->getDeputyType(), [Deputy::DEPUTY_TYPE_PA, Deputy::DEPUTY_TYPE_PROF])) {
                    $validationGroups[] = 'order-org-deputy';
                }

                return $validationGroups;
            }
        ));
    }
}
