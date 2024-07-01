<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use App\Validator\Password;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class,  [
            'type' => PasswordType::class,
            'first_options' => [
                'label' => 'Nouveau mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'second_options' => [
                'label' => 'Confirmer le mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'invalid_message' => 'Les mots de passe ne correspondent pas. Veuillez vérifier et réessayer.',
            'mapped' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir un mot de passe valide.',
                ]),
                new Password([
                    'message' => 'Votre mot de passe doit contenir au moins une majuscule, un chiffre et un caractère alphanumérique.'
                ]),
                new Length([
                    'min' => 10,
                    'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} charactères.',
                    'max' => 32,
                ]),
            ],
            'trim' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}