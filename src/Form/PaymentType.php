<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('invoice', EntityType::class, [
                'class' => Invoice::class,
                'choice_label' => 'invoice_number',
                'label' => false,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Payée' => 'Paid',
                    'En attente' => 'Pending',
                ],
                'label' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
