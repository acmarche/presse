<?php

namespace AcMarche\Presse\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
     /*  $builder->add('file', DropzoneType::class, [
            'attr' => [
                'placeholder' => 'Cliquez ici pour sélectioner les images',
            ],
            'label' => false,
            'multiple' => true,
        ]);*/
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
            ],
        );
    }
}
