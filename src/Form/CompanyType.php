<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\LegalForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('denomination')
            ->add('siren')
            ->add('siret')
            ->add('tva_number')
            ->add('rcs_number')
            ->add('phone_number')
            ->add('mail')
            ->add('creation_date', null, [
                'widget' => 'single_text',
            ])
            ->add('registered_social')
            ->add('sector')
            ->add('logo')
            ->add('signing')
            ->add('legal_form', EntityType::class, [
                'class' => LegalForm::class,
                'choice_label' => 'name',
            ])
            ->add('address', AddressType::class, [
                'data_class' => Address::class,
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
