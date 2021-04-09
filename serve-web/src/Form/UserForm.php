<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class)
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'user.firstName.notBlank']),
                ]
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'user.lastName.notBlank']),
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
            ])
            ->add('roleName', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Case manager' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN'
                ]
            ])
            ->add('submit', SubmitType::class);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user->isAdmin()) {
                $form->get('roleName')->setData('ROLE_ADMIN');
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            $roleName = $form->get('roleName')->getData();

            if ($roleName === 'ROLE_ADMIN') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles([]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'translation_domain' => 'forms'
        ));
    }
}
