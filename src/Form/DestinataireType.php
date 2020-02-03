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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'nom',
                TextType::class,
                [

                ]
            )
            ->add(
                'prenom',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add('email', EmailType::class)
            ->add(
                'exterieur',
                CheckboxType::class,
                [
                    'required' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Destinataire::class,
            ]
        );
    }
}
