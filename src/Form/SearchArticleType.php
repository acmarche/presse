<?php

namespace AcMarche\Presse\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'keyword',
                SearchType::class,
                [
                    'label' => 'Mot clef',
                    'attr' => [
                        'placeholder' => 'Rechercher',
                    ],
                    
                ]
            )
            ->add('year', IntegerType::class, [
                'label' => 'Année',
                'required' => false,

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                // Configure your form options here
            ]
        );
    }
}
