<?php

namespace ModInventario\Form;

use ModInventario\Entity\Almacen;
use ModInventario\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductoType extends AbstractType
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
            ->add('unidadMedida', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                    'data-validate' => 'required',
                    'data-autocomplete' => 'Combo,Juego,Kit,Par,Paquete,Unidad,Metros'
                ]
            ])
            ->add('activo', CheckboxType::class, [
                'label' => '¿Está activo?',
                'required' => false,
                'attr' => [
                    'data-role' => 'switch'
                ]
            ])
            ->add(
                $builder->create('precioCompra', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => 'Precio de compra'
                ])
                    ->add('precioCompraCup', TextType::class, [
                        'label' => 'CUP',
                        'attr' => [
                            'class' => 'metro-input text-right',
                            'data-prepend' => '<span class="mif-dollar2"></span>',
                            'data-role' => 'input',
                            'data-validate' => 'required'
                        ]
                    ])
                    ->add('precioCompraMlc', TextType::class, [
                        'label' => 'MLC',
                        'attr' => [
                            'class' => 'metro-input text-right',
                            'data-prepend' => '<span class="mif-dollar2"></span>',
                            'data-role' => 'input',
                        ]
                    ])
            )
            ->add(
                $builder->create('precioVenta', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => 'Precio de venta'
                ])
                    ->add('precioVentaCup', TextType::class, [
                        'label' => 'CUP',
                        'attr' => [
                            'class' => 'metro-input text-right',
                            'data-prepend' => '<span class="mif-dollar2"></span>',
                            'data-role' => 'input',
                            'data-validate' => 'required'
                        ]
                    ])
                    ->add('precioVentaMlc', TextType::class, [
                        'label' => 'MLC',
                        'attr' => [
                            'class' => 'metro-input text-right',
                            'data-prepend' => '<span class="mif-dollar2"></span>',
                            'data-role' => 'input'
                        ]
                    ])
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}
