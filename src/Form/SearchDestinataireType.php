<?php

namespace AcMarche\Presse\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchDestinataireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                SearchType::class,
                [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'Nom',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                ],
            )
            ->add('attachment', ChoiceType::class, [
                'label' => 'Pièce jointe',
                'required' => false,
                'choices' => ['oui' => true, 'non' => false],
            ])
            ->add('notification', ChoiceType::class, [
                'label' => 'Receoir la revue de presse',
                'required' => false,
                'choices' => ['oui' => true, 'non' => false],
            ])
            ->add('externe', ChoiceType::class, [
                'label' => 'Externe',
                'help' => 'N est pas lié à un compte ville',
                'required' => false,
                'choices' => ['oui' => true, 'non' => false],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                // Configure your form options here
            ],
        );
    }
}
