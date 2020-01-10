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

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'help' => 'An activation email will be sent to the provided email address'
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('roleName', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Case manager' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'common.submit.label'
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if (in_array('ROLE_ADMIN', $user->getRoles())) {
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
