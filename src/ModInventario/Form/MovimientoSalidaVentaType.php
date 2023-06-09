<?php

namespace ModInventario\Form;

use ModInventario\Entity\Organizacion;
use ModInventario\Request\MovimientoVentaRequest;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovimientoSalidaVentaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fecha', TextType::class, [
                'label' => 'Fecha',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'calendarpicker',
                    'data-max-date' => date('d-m-Y'),
                    'data-locale' => 'es-ES',
                    'data-input-format' => '%d-%m-%Y',
                    'data-format' => '%d-%m-%Y',
                    'data-validate' => 'required'
                ],
            ])
            ->add('cliente', EntityType::class, [
                'class' => Organizacion::class,
                'choice_label' => 'nombre',
                'label' => 'Cliente',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'select'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('cliente_proveedor')
                        ->where('cliente_proveedor.esCliente = TRUE')
                        ->orderBy('cliente_proveedor.nombre', 'ASC');
                },
                'placeholder' => 'Seleccione',
            ])
            ->add('formaPago', ChoiceType::class, [
                'choices' => [
                    'Efectivo' => 'Efectivo',
                    'Transferencia' => 'Transferencia',
                ],
                'label' => 'Forma de pago',
                'placeholder' => 'Seleccione',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'select'
                ]
            ])
            ->add(
                $builder->create('entregado_por', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => 'Entregado por'
                ])
                    ->add('entregado_por_nombre', TextType::class, [
                        'label' => 'Nombre',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('entregado_por_cargo', TextType::class, [
                        'label' => 'Cargo',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('entregado_por_ci', TextType::class, [
                        'label' => 'Carné de identidad',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input',
                            ##'data-validate' => 'not required digits length=11'
                        ],
                    ])
                    ->add('entregado_por_fecha', TextType::class, [
                        'label' => 'Fecha',
                        'attr' => [
                            'class' => 'metro-input',
                            'data-role' => 'calendarpicker',
                            'data-max-date' => date('d-m-Y'),
                            'data-locale' => 'es-ES',
                            'data-input-format' => '%d-%m-%Y',
                            'data-format' => '%d-%m-%Y',
                        ],
                    ])
            )
            ->add(
                $builder->create('transportado_por', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => 'Transportado por'
                ])
                    ->add('transportado_por_nombre', TextType::class, [
                        'label' => 'Nombre',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('transportado_por_cargo', TextType::class, [
                        'label' => 'Cargo',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('transportado_por_ci', TextType::class, [
                        'label' => 'Carné de identidad',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input',
                            ##'data-validate' => 'not required digits length=11'
                        ],
                    ])
                    ->add('transportado_por_fecha', TextType::class, [
                        'label' => 'Fecha',
                        'attr' => [
                            'class' => 'metro-input',
                            'data-role' => 'calendarpicker',
                            'data-max-date' => date('d-m-Y'),
                            'data-locale' => 'es-ES',
                            'data-input-format' => '%d-%m-%Y',
                            'data-format' => '%d-%m-%Y',
                        ],
                    ])
            )
            ->add(
                $builder->create('recibido_por', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => 'Recibido por'
                ])
                    ->add('recibido_por_nombre', TextType::class, [
                        'label' => 'Nombre',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('recibido_por_cargo', TextType::class, [
                        'label' => 'Cargo',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('recibido_por_ci', TextType::class, [
                        'label' => 'Carné de identidad',
                        'required' => false,
                        'attr' => [
                            'class' => 'metro-input',
                            ##'data-validate' => 'not required digits length=11'
                        ],
                    ])
                    ->add('recibido_por_fecha', TextType::class, [
                        'label' => 'Fecha',
                        'attr' => [
                            'class' => 'metro-input',
                            'data-role' => 'calendarpicker',
                            'data-max-date' => date('d-m-Y'),
                            'data-locale' => 'es-ES',
                            'data-input-format' => '%d-%m-%Y',
                            'data-format' => '%d-%m-%Y',
                        ],
                    ])
            )
            ->add('descripcion', TextType::class, [
                'label' => 'Descripción',
                'attr' => [
                    'class' => 'metro-input'
                ],
            ])
            ->add('almacen_producto_movimientos', CollectionType::class, [
                'entry_type' => AlmacenProductoMovimientoType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => [
                    'label' => false,
                    'attr' => [ 'class' => 'd-flex almacen-producto-movimiento-fila'],
                    'precio' => 'venta'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MovimientoVentaRequest::class,
        ]);
    }
}
