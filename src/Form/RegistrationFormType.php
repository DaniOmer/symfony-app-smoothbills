<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\EscapeCharacter;
use App\Validator\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
            - Callback pour gérer match password et confirmedPassword
            - Comprendre pourquoi le 'toggle' => true ne fonctionne pas
        */


        $builder
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un prénom valide.',
                    ]),
                    new Length(['min' => 3]),
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ]
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nom valide.',
                    ]),
                    new Length(['min' => 3]),
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un email valide.',
                    ]),
                ]
            ])
            ->add('password', RepeatedType::class,  [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas. Veuillez vérifier et réessayer.',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
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
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
