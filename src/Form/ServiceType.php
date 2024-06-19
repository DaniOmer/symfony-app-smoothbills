<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\ServiceStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un nom valide.']),
                    new Length(['min' => 3]),
                ],
                'trim' => true,
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
            ])
            ->add('estimated_duration', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une durée estimée valide.']),
                ],
                'trim' => true,
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Length(['min' => 10]),
                ],
                'trim' => true,
                'required' => false,
            ])
            ->add('serviceStatus', EntityType::class, [
                'class' => ServiceStatus::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un statut',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}