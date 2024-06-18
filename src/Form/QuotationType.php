<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Quotation;
use App\Entity\QuotationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('date', DateType::class, [
            //     'widget' => 'single_text',
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'La date ne doit pas être vide.',
            //         ]),
            //         new Date([
            //             'message' => 'La date doit être valide.',
            //         ]),
            //     ],
            // ])
            ->add('date', null, [
                'widget' => 'single_text'
            ])
            ->add('type', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le type ne doit pas être vide.',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le type ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('quotation_status', QuotationStatusType::class, [
                'data_class' => QuotationStatus::class,
                'label' => false
            ])
            ->add('company', CompanyType::class, [
                'data_class' => Company::class,
                'label' => false
            ])
            ->add('customer', CustomerType::class, [
                'data_class' => Customer::class,
                'label' => false
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setType(ucfirst(strtolower($data->getType())));
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