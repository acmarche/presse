<?php

namespace AcMarche\Presse\Form;

use AcMarche\Presse\Entity\Album;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class AlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'dateAlbum',
                DateType::class,
                [
                    'label' => 'Date de l\'album',
                    'help' => 'Pour un nouveau mois, laisser le jour sur 1',
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'nom',
                TextType::class,
                [
                    'required' => false,
                    'help' => 'Si ce champ est vide, le nom de l\'album sera sa date',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'attr' => ['rows' => 5],
                ]
            )
            ->add(
                'image',
                VichImageType::class,
                [
                    'required' => false,
                    'label' => 'Image de couverture',
                    'help' => 'Par défaut ce sera l\'image du premier article de l\'album',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Album::class,
            ]
        );
    }
}
