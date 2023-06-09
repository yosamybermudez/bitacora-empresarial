<?php

namespace ModInventario\Form;

use ModInventario\Entity\AlmacenProductoMovimiento;
use ModInventario\Entity\Producto;
use ModInventario\Entity\ProductoMovimiento;
use ModInventario\Request\ProductoMovimientoRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductoMovimientoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'nombre',
                'choice_attr' => function (?Producto $producto) use ($options) {
                    return $producto ? [
                        'data-precio-cup' =>  $options['precio'] === 'venta'  ? $producto->getPrecioVentaCup() : $producto->getPrecioCompraCup(),
                        'data-precio-mlc' =>  $options['precio'] === 'venta'  ? $producto->getPrecioVentaMlc() : $producto->getPrecioCompraMlc(),
                    ] : [];
                },
                'label' => false,
                'row_attr' => [
                    'class' => 'mr-2'
                ],
                'placeholder' => 'Seleccione',
                'attr' => [
                    'class' => 'metro-input required producto_nombre',
                    'data-role' => 'select',
                    'data-validate' => 'required number'
                ],
            ])
            ->add('cantidad', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'metro-input producto_cantidad',
                ],
                'row_attr' => [
                    'class' => 'mr-2'
                ]
            ])
            ->add('eliminar', ButtonType::class, [
                'label' => '<span class="mif-bin fg-white"></span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'button alert producto-movimiento-fila-eliminar'
                ]

            ])
        ;

        if($options['precio_inputs']){
            $builder
                ->add('precioCupVigente', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'metro-input required almacen_producto_precio_cup_vigente',
                        'data-validate' => 'required number'
                    ],
                    'row_attr' => [
                        'class' => 'mr-2'
                    ]
                ])
                ->add('precioMlcVigente', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'metro-input required almacen_producto_precio_mlc_vigente',
                        'data-validate' => 'required number'
                    ],
                    'row_attr' => [
                        'class' => 'mr-2'
                    ]
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductoMovimiento::class,
            'precio' => 'venta',
            'precio_inputs' => false
        ]);
    }
}
