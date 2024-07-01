<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Quotation;
use App\Entity\QuotationStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class QuotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Paiement unique' => 'OneTime',
                    'Paiement récurrent' => 'Recurring',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le type ne doit pas être vide.',
                    ]),
                ],
                'trim' => true,
                'label' => false,
            ])
            ->add('quotation_status', EntityType::class, [
                'class' => QuotationStatus::class,
                'choice_label' => 'name',
                'label' => false,
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'name',
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir un client enregistré.',
                    ]),
                ],
            ])
            ->add('sendOption', ChoiceType::class, [
                'mapped' => false,
                'label' => false,
                'choices' => [
                    'Envoyer maintenant' => 'Maintenant',
                    'Envoyer plus tard' => 'Plus tard',
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir une option d\'envoi.',
                    ]),
                ],
            ])
            ->add('quotationHasServices', CollectionType::class, [
                'entry_type' => QuotationHasServiceType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
                'attr' => ['class' => 'custom-class', 'style' => 'display:none;'],
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Vous devez ajouter au moins un service.',
                    ]),
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quotation::class,
        ]);
    }
}