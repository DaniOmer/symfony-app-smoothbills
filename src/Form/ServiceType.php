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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use App\Validator\EscapeCharacter;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un nom valide.']),
                    new Length(['min' => 3]),
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ],
                'trim' => true,
            ])
            ->add('price', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un prix valide.']),
                    new Regex([
                        'pattern' => '/^\d+(\.\d{1,2})?$/',
                        'message' => 'Le prix n\'est pas valide.',
                    ]),
                ],
            ])
            ->add('estimated_duration', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une durée estimée valide.']),
                    new Length(['min' => 3]),
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ],
                'trim' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'constraints' => [
                    new Length(['min' => 10]),
                    new NotBlank(['message' => 'Veuillez saisir une description valide.'])
                ],
                'trim' => true,
                'required' => false,
            ])
            ->add('serviceStatus', EntityType::class, [
                'label' => false,
                'class' => ServiceStatus::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un statut',
                'required' => true,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $service = $event->getData();
            $service->setName(ucfirst(strtolower($service->getName())));
            $event->setData($service);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}