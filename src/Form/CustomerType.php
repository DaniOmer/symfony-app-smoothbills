<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Customer;
use App\Validator\EscapeCharacter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nom valide.',
                    ]),
                    new Length(['min' => 3]),
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ],
                'trim' => true,
            ])
            ->add('mail', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Veuillez fournir une adresse e-mail valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'Le numéro de téléphone n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices'  => [
                    'Personne' => 'Personne',
                    'Entreprise' => 'Entreprise',
                ],
                'trim' => true,
            ])
            ->add('address', AddressType::class, [
                'data_class' => Address::class,
                'label' => false
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setName(ucfirst(strtolower($data->getName())));
            $data->setMail(strtolower($data->getMail()));
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
