<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\User;
use App\Form\DataTransformer\FileToUrlTransformer;
use App\Validator\EscapeCharacter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\Dropzone\Form\DropzoneType;

class UserType extends AbstractType
{
    public function __construct(
        private FileToUrlTransformer $fileToUrlTransformer
    ){
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Veuillez fournir une adresse e-mail valide.',
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez saisir un email valide.',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('first_name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un prénom valide.',
                    ]),
                    new Length(['min' => 3]),
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ],
                'trim' => true,
            ])
            ->add('last_name', TextType::class, [
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
            ->add('avatar', DropzoneType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Cliquez pour télécharger ou drag and drop.',
                    'data-controller' => 'dropzone',
                ],
            ])
            ->add('job_title', TextType::class, [
                'label' => false,
                'constraints' => [
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ],
                'trim' => true,
            ])
        ;

        $builder->get('avatar')->addModelTransformer($this->fileToUrlTransformer);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setFirstName(ucfirst(strtolower($data->getFirstName()))); ;
            $data->setLastName(ucfirst(strtolower($data->getLastName())));
            $data->setEmail(strtolower($data->getEmail()));
            $event->setData($data);
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            
            if ($data instanceof User && $data->getAvatar()) {
                $data->setAvatar($data->getAvatar());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
