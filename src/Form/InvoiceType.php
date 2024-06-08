<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Entity\InvoiceStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text'
            ])
            ->add('invoiceStatus', EntityType::class, [
                'class' => InvoiceStatus::class,
                'choice_label' => 'id',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
