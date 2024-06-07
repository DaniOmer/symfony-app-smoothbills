<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
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
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('email')
            ->add('password', RepeatedType::class,  [
                'type' => PasswordType::class,
                // 'toggle' => true,
                // 'use_toggle_form_theme' => false,
                // 'hidden_label' => 'Masquer',
                // 'visible_label' => 'Afficher',
                'invalid_message' => 'Les mots de passe ne correspondent pas. Veuillez vérifier et réessayer.',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un mot de passe valide',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
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
