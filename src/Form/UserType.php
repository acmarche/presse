<?php

namespace AcMarche\Presse\Form;

use AcMarche\Presse\Entity\User;
use AcMarche\Presse\Service\PresseService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('username')
            ->add('email')
            ->add('password')
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'choices' => array_combine(PresseService::getRoles(), PresseService::getRoles()),
                    'required' => true,
                    'multiple' => true,
                    'expanded' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
