<?php

namespace App\Form;

use App\Entity\QuotationHasService;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Doctrine\ORM\EntityRepository;

class QuotationHasServiceType extends AbstractType
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
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'label' => 'Nom du service',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir un service enregistré.',
                    ]),
                ],
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('s')
                        ->where('s.company = :company')
                        ->setParameter('company', $user->getCompany());
                },
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une quantité valide.',
                    ]),
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]+$/',
                        'message' => 'La quatité saisie n\'est pas valide.',
                    ]),
                ],
                'trim' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuotationHasService::class,
        ]);
    }
}
