<?php

namespace App\Form;

use App\Entity\Article;
use App\Form\DataTransformer\FileToUrlTransformer;
use App\Validator\EscapeCharacter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ArticleType extends AbstractType
{
    public function __construct(
        private FileToUrlTransformer $fileToUrlTransformer
    ){
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un titre valide.',
                    ]),
                    new EscapeCharacter([
                        'message' => 'Le champ ne peut pas contenir de caractères spéciaux.'
                    ]),
                ],
                'trim' => true,
                'label' => false,
            ])
            ->add('content', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un contenu valide.',
                    ]),
                ],
                'trim' => true,
                'label' => false,
            ])
            ->add('thumbnail', DropzoneType::class, [
                'attr' => [
                    'placeholder' => 'Cliquez pour télécharger ou drag and drop.',
                    'data-controller' => 'dropzone',
                ],
                'trim' => true,
                'label' => false,
            ])
        ;

        $builder->get('thumbnail')->addModelTransformer($this->fileToUrlTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
