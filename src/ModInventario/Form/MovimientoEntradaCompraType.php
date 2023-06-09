<?php

namespace ModInventario\Form;

use ModInventario\Entity\Almacen;
use ModInventario\Entity\Organizacion;
use ModInventario\Request\MovimientoEntradaRequest;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovimientoEntradaCompraType extends AbstractType
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
            ->add('almacen_destino', EntityType::class, [
                'class' => Almacen::class,
                'choice_label' => 'nombre',
                'label' => 'Almacén destino de productos',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'select',
                    'data-validate' => 'required'
                ],
                'placeholder' => 'Seleccione'
            ])
            ->add('proveedor', EntityType::class, [
                'class' => Organizacion::class,
                'choice_label' => 'nombre',
                'label' => 'Proveedor',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'select',
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('cliente_proveedor')
                        ->where('cliente_proveedor.esProveedor = TRUE')
                        ->orderBy('cliente_proveedor.nombre', 'ASC');
                },
                'placeholder' => 'Seleccione',
                'required' => true,
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
                    'data-role' => 'select',
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
                            #'data-validate' => 'not required digits length=11'
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
                            #'data-validate' => 'not required digits length=11'
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
                            #'data-validate' => 'not required digits length=11'
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
            ->add('producto_movimientos', CollectionType::class, [
                'entry_type' => ProductoMovimientoType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => [
                    'precio' => 'compra',
                    'precio_inputs' => true,
                    'label' => false,
                    'attr' => [ 'class' => 'd-flex producto-movimiento-fila']
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MovimientoEntradaRequest::class,
        ]);
    }
}
