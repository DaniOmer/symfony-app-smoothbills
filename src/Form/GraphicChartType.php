<?php

namespace App\Form;

use App\Entity\Font;
use App\Entity\GraphicChart;
use App\Form\DataTransformer\FileToUrlTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class GraphicChartType extends AbstractType
{
    public function __construct(
        private FileToUrlTransformer $fileToUrlTransformer
    ){
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyLogo', DropzoneType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Cliquez pour télécharger ou drag and drop.',
                    'data-controller' => 'dropzone',
                ],
            ])
            ->add('backgroundColor', ColorType::class, [
                'label' => false,
            ])
            ->add('titleColor', ColorType::class, [
                'label' => false,
            ])
            ->add('titleFont', EntityType::class, [
                'class' => Font::class,
                'choice_label' => 'name',
                'label' => false,
            ])
            ->add('contentFont', EntityType::class, [
                'class' => Font::class,
                'choice_label' => 'name',
                'label' => false,
            ])
        ;

        $builder->get('companyLogo')->addModelTransformer($this->fileToUrlTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GraphicChart::class,
        ]);
    }
}
