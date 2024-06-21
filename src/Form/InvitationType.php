<?php

namespace App\Form;

use App\Entity\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'label' => false,
            'constraints' => [
                new Email([
                    'message' => 'Veuillez fournir une adresse e-mail valide.',
                ]),
                new NotBlank([
                    'message' => 'Veuillez saisir un email valide.',
                ]),
            ],
            'trim' => true,
        ])
        ->add('role', ChoiceType::class, [
            'label' => false,
            'choices'  => [
                'Editeur' => 'ROLE_EDITOR',
                'Comptable' => 'ROLE_ACCOUNTANT',
            ],
            'trim' => true,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invitation::class,
        ]);
    }
}
