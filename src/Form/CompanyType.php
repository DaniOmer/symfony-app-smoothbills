<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\LegalForm;
use App\Form\DataTransformer\FileToUrlTransformer;
use App\Validator\EscapeCharacter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\UX\Dropzone\Form\DropzoneType;

class CompanyType extends AbstractType
{
    public function __construct(
        private FileToUrlTransformer $fileToUrlTransformer
    ){
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('denomination', TextType::class, [
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
            ->add('siren', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'Le numéro siren n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('siret', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'Le numéro siret n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('tva_number', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'Le numéro de TVA n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('rcs_number', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'Le numéro RCS n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('phone_number', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'Le numéro de téléphone n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('mail', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'Veuillez fournir une adresse e-mail valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('creation_date', null, [
                'widget' => 'single_text',
            ])
            ->add('registered_social', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'Le capital social n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('sector', ChoiceType::class, [
                'choices'  => [
                    'Technologie' => 'Technology',
                    'Finance' => 'Finance',
                    'Education' => 'Education',
                    'Transport' => 'Transportation',
                    'Santé' => 'Healthcare',
                    'Autre' => 'Other',
                ],
                'trim' => true,
            ])
            // ->add('signing', DropzoneType::class, [
            //     'attr' => [
            //         'placeholder' => 'Cliquez pour télécharger ou drag and drop.',
            //         'data-controller' => 'dropzone',
            //     ],
            // ])
            // ->add('logo', DropzoneType::class, [
            //     'attr' => [
            //         'placeholder' => 'Cliquez pour télécharger ou drag and drop.',
            //         'data-controller' => 'dropzone',
            //     ],
            // ])
            ->add('legal_form', EntityType::class, [
                'class' => LegalForm::class,
                'choice_label' => 'name',
            ])
            ->add('address', AddressType::class, [
                'data_class' => Address::class,
                'label' => false
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setDenomination(ucfirst(strtolower($data->getDenomination())));
            $data->setMail(strtolower($data->getMail()));
            $event->setData($data);
        });

        // $builder->get('signing')->addModelTransformer($this->fileToUrlTransformer);

        // $builder->get('logo')->addModelTransformer($this->fileToUrlTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
