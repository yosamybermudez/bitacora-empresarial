<?php

namespace ModInventario\Form;

use ModInventario\Entity\Almacen;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlmacenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                    'data-validate' => 'required'
                ]
            ])
            ->add('codigo', TextType::class, [
                'label' => 'Código',
                'attr' => [
                    'class' => 'metro-input',
                    'readonly' => 'readonly'
                ]
            ])
            ->add('descripcion', TextType::class, [
                'label' => 'Descripción',
                'attr' => [
                    'class' => 'metro-input'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Almacen::class,
        ]);
    }
}
