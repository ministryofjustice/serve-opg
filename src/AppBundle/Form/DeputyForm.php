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
                'label' => 'deputy.type.label',
                'translation_domain' => 'forms',
                'required' => false,
                'choices' => [
                    'deputy.type.pleaseSelect' => '',
                    'deputy.type.LAY' => Deputy::DEPUTY_TYPE_LAY,
                    'deputy.type.PUBLIC_AUTHORITY' => Deputy::DEPUTY_TYPE_PA,
                    'deputy.type.PROFESSIONAL' => Deputy::DEPUTY_TYPE_PROF
                ]
            ])
            ->add('forename', TextType::class, [
                'label' => 'deputy.forename',
                'translation_domain' => 'forms',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('surname', TextType::class, [
                'label' => 'deputy.surname',
                'translation_domain' => 'forms',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('dateOfBirth', BirthdayType::class, [
                'label' => 'deputy.dateOfBirth.label',
                'translation_domain' => 'forms',
                'required' => false,
                'widget' => 'text',
                'placeholder' => array(
                    'day' => 'Day','month' => 'Month' , 'year' => 'Year'
                ),
                'format' => 'dd-MM-yyyy',
            ])
            ->add('emailAddress', TextType::class, [
                'label' => 'deputy.emailAddress',
                'translation_domain' => 'forms',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('daytimeContactNumber', TextType::class, [
                'label' => 'deputy.daytimeContactNumber',
                'translation_domain' => 'forms',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('eveningContactNumber', TextType::class, [
                'label' => 'deputy.eveningContactNumber',
                'translation_domain' => 'forms',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('mobileContactNumber', TextType::class, [
                'label' => 'deputy.mobileContactNumber',
                'translation_domain' => 'forms',
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
                'label' => 'deputy.addressTown',
                'translation_domain' => 'forms',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressCounty', TextType::class, [
                'label' => 'deputy.addressCounty',
                'translation_domain' => 'forms',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressPostcode', TextType::class, [
                'label' => 'deputy.addressPostcode',
                'translation_domain' => 'forms',
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
