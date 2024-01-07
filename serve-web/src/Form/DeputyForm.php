<?php

namespace App\Form;

use App\Entity\Deputy;
use App\Entity\Order;
use App\Entity\Post;
use App\Common\Form\Answers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeputyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $deputyTypeValue = !empty($options['deputyType']) ? $options['deputyType'] : '';

        $builder
            ->add('deputyType', ChoiceType::class, [
                'label' => 'deputy.type.label',
                'required' => true,
                'choices' => [
                    'deputy.type.pleaseSelect' => '',
                    'deputy.type.LAY' => Deputy::DEPUTY_TYPE_LAY,
                    'deputy.type.PUBLIC_AUTHORITY' => Deputy::DEPUTY_TYPE_PA,
                    'deputy.type.PROFESSIONAL' => Deputy::DEPUTY_TYPE_PROF
                ],
                'data' => $deputyTypeValue
            ])
            ->add('forename', TextType::class, [
                'label' => 'deputy.forename',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('surname', TextType::class, [
                'label' => 'deputy.surname',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('emailAddress', TextType::class, [
                'label' => 'deputy.emailAddress.label',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('daytimeContactNumber', TextType::class, [
                'label' => 'deputy.daytimeContactNumber',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('eveningContactNumber', TextType::class, [
                'label' => 'deputy.eveningContactNumber',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('mobileContactNumber', TextType::class, [
                'label' => 'deputy.mobileContactNumber',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressLine1', TextType::class, [
                'required' => true,
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
                'required' => true,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressCounty', TextType::class, [
                'label' => 'deputy.addressCounty',
                'required' => false,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('addressPostcode', TextType::class, [
                'label' => 'deputy.addressPostcode',
                'required' => true,
                'attr' => ['maxlength'=> 255]
            ])
            ->add('saveAndContinue', SubmitType::class);

        if ($deputyTypeValue === 'LAY') {
            $builder->add('dateOfBirth', BirthdayType::class, [
                'label' => 'deputy.dateOfBirth.label',
                'required' => true,
                'widget' => 'text',
                'placeholder' => array(
                    'day' => 'Day','month' => 'Month' , 'year' => 'Year'
                ),
                'format' => 'dd-MM-yyyy',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Deputy::class,
            'translation_domain' => 'forms',
            'validation_groups' => function (FormInterface $form): array {

                /* @var $data \App\Entity\Deputy */
                $data = $form->getData();
                $validationGroups = ['order-deputy'];

                if (in_array($data->getDeputyType(), [Deputy::DEPUTY_TYPE_PA, Deputy::DEPUTY_TYPE_PROF])) {
                    $validationGroups[] = 'order-org-deputy';
                }

                return $validationGroups;
            },
            'deputyType' => ''
        ));
    }
}
