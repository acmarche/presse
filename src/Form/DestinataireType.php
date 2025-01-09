<?php

namespace AcMarche\Presse\Form;

use AcMarche\Presse\Entity\Destinataire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DestinataireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'nom',
                TextType::class,
                [
                ],
            )
            ->add(
                'prenom',
                TextType::class,
                [
                    'label' => 'Prénom',
                    'required' => false,
                ],
            )
            ->add('email', EmailType::class)
            ->add(
                'attachment',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Attacher les articles en pièce jointe',
                ],
            )
            ->add(
                'notification',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Recevoir la revue de presse',
                    'help' => 'Décochez cette case pour ne pas recevoir la revue de presse',
                ],
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Destinataire::class,
            ],
        );
    }
}
