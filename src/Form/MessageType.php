<?php

namespace AcMarche\Presse\Form;

use AcMarche\Presse\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Contenu du mail',
                'attr' => ['rows' => 5],
            ])
            ->add('file', VichFileType::class, [
                'label' => 'Pièce jointe',
                'allow_delete' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Message::class,
            ],
        );
    }
}
