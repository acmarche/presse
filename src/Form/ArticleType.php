<?php

namespace AcMarche\Presse\Form;

use AcMarche\Presse\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'attr' => [
                        'rows' => 5,
                    ],
                ],
            )
            ->add(
                'dateArticle',
                DateType::class,
                [
                ],
            )
            ->add('file', DropzoneType::class, [
                'attr' => [
                    'placeholder' => 'Cliquez ici pour sélectioner les images',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Article::class,
            ],
        );
    }
}
