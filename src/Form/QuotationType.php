<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Quotation;
use App\Entity\QuotationStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;

class QuotationType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Paiement unique' => 'Unique',
                    'Paiement récurrent' => 'Recurrent',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le type ne doit pas être vide.',
                    ]),
                ],
                'trim' => true,
                'label' => false,
                'required' => true,
            ])
            ->add('quotation_status', EntityType::class, [
                'class' => QuotationStatus::class,
                'choice_label' => 'name',
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir un statut de devis.',
                    ]),
                ],
                'required' => true,
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
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('s')
                        ->where('s.company = :company')
                        ->setParameter('company', $user->getCompany());
                },
                'required' => true,
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
            ]);

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
