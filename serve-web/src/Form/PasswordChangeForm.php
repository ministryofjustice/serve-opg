<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PasswordChangeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'first_options' => [
                    'label' => 'user.passwordChange.password.label',
                    'hint' => 'user.passwordChange.password.hint',
                    'hintList' => 'user.passwordChange.password.hintList'
                ],
                'second_options' => ['label' => 'user.passwordChange.passwordConfirm.label'],
                'invalid_message' => 'user.password.doesNotMatch',
                'type' => PasswordType::class,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'user.password.notBlank']),
                    new Length(['min' => 6, 'max' => 50, 'minMessage' => 'user.password.minLength', 'maxMessage' => 'user.password.maxLength']),
                    new Regex(['pattern' => '/[a-z]/', 'message' => 'user.password.noLowerCaseChars']),
                    new Regex(['pattern' => '/[A-Z]/', 'message' => 'user.password.noUpperCaseChars']),
                    new Regex(['pattern' => '/[0-9]/', 'message' => 'user.password.noNumber']),
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'user.passwordChange.submit.label',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }
}
