<?php

namespace ModInventario\Form;

use ModInventario\Entity\AlmacenProducto;
use ModInventario\Entity\AlmacenProductoMovimiento;
use ModInventario\Entity\Producto;
use ModInventario\Entity\ProductoMovimiento;
use ModInventario\Request\ProductoMovimientoRequest;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlmacenProductoMovimientoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('almacen_producto', EntityType::class, [
                'class' => AlmacenProducto::class,
                'choice_label' => 'nombreCompleto',
                'choice_attr' => function (?AlmacenProducto $almacenProducto) use ($options) {
                    return $almacenProducto ? [
                        'data-precio-cup' =>  $options['precio'] === 'venta'  ? $almacenProducto->getProducto()->getPrecioVentaCup() : $almacenProducto->getProducto()->getPrecioCompraCup(),
                        'data-precio-mlc' =>  $options['precio'] === 'venta'  ? $almacenProducto->getProducto()->getPrecioVentaMlc() : $almacenProducto->getProducto()->getPrecioCompraMlc(),
                        'data-saldo-disponible' => $almacenProducto->getSaldoDisponible()
                    ] : [];
                },
                'label' => false,
                'row_attr' => [
                    'class' => 'mr-2'
                ],
                'placeholder' => 'Seleccione',
                'attr' => [
                    'class' => 'metro-input required almacen_producto_nombre',
                    'data-role' => 'select',
                    'data-validate' => 'required number'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('almacen_producto')
                        ->innerJoin('almacen_producto.producto', 'producto')
                        ->where('almacen_producto.saldoDisponible != 0 and almacen_producto.saldoDisponible > 0')
                        ->orderBy('producto.nombre', 'ASC');
                }
            ])
            ->add('cantidad', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'metro-input required almacen_producto_cantidad',
                    'data-validate' => 'required number'
                ],
                'row_attr' => [
                    'class' => 'mr-2'
                ]
            ])
            ->add('eliminar', ButtonType::class, [
                'label' => '<span class="mif-bin fg-white"></span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'button alert almacen-producto-movimiento-fila-eliminar'
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
            'data_class' => AlmacenProductoMovimiento::class,
            'precio' => 'venta',
            'precio_inputs' => true
        ]);
    }
}
