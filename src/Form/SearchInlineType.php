<?php

namespace AcMarche\Presse\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchInlineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add(
                'keyword',
                SearchType::class,
                [
                    'required' => true,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Rechercher...',
                        'autocomplete' => 'off',
                        'data-autocomplete-target' => 'input',
                        'class' => 'me-2',
                    ],
                ],
            );
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([]);
    }
}