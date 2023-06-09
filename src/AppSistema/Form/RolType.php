<?php

namespace AppSistema\Form;

use AppSistema\Entity\Rol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identificador', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                    'readonly' => true
                ]
            ])
            ->add('nombre', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                    'data-validate' => 'required'
                ]
            ])
            ->add('descripcion', TextType::class, [
                'label' => 'Descripción',
                'attr' => [
                    'class' => 'metro-input',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rol::class,
        ]);
    }
}
