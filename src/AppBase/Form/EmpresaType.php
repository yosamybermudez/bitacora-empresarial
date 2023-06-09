<?php

namespace AppBase\Form;

use AppBase\Entity\Organizacion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class EmpresaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                    'data-validate' => 'required'
                ],
                'required' => true
            ])
            ->add('sector', TextType::class, [
                'attr' => [
                    'class' => 'metro-input'
                ],
                'required' => false
            ])
            ->add('logoFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_link' => false,
                'label' => 'Logotipo',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'file',
                    'data-mode' => 'drop',
                    'data-button-title' => 'Seleccione el logotipo de la empresa'
                ],
            ])
            ->add('contactoPrincipal', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                ]
            ])
            ->add('descripcion', TextType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'metro-input'
                ],
            ])
            ->add('esCliente', CheckboxType::class, [
                'label' => '¿Es cliente?',
                'required' => false,
                'attr' => [
                    'data-role' => 'switch'
                ]
            ])
            ->add('esProveedor', CheckboxType::class, [
                'label' => '¿Es proveedor?',
                'required' => false,
                'attr' => [
                    'data-role' => 'switch'
                ]
            ])
            ->add('domicilio', TextType::class, [
                'attr' => [
                    'class' => 'metro-input'
                ]
            ])
            ->add('telefonos', TextType::class, [
                'label' => 'Teléfono(s)',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                ]
            ])
            ->add('correosElectronicos', TextType::class, [
                'label' => 'Correo(s) electrónico(s)',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                ],
                'required' => false

            ])
            ->add(
                $builder->create('cuenta_cup', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => 'Cuenta CUP'
                ])
                    ->add('cuentaCupNumero', TextType::class, [
                        'label' => 'Número',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaCupTitular', TextType::class, [
                        'label' => 'Titular',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaCupBancoNombre', TextType::class, [
                        'label' => 'Banco',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaCupBancoSucursal', TextType::class, [
                        'label' => 'Sucursal',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaCupBancoDireccion', TextType::class, [
                        'label' => 'Dirección',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                        'required' => false
                    ])
            )
            ->add(
                $builder->create('cuenta_mlc', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => 'Cuenta MLC'
                ])
                    ->add('cuentaMlcNumero', TextType::class, [
                        'label' => 'Número',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaMlcTitular', TextType::class, [
                        'label' => 'Titular',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaMlcBancoNombre', TextType::class, [
                        'label' => 'Banco',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaMlcBancoSucursal', TextType::class, [
                        'label' => 'Sucursal',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
                    ->add('cuentaMlcBancoDireccion', TextType::class, [
                        'label' => 'Dirección',
                        'attr' => [
                            'class' => 'metro-input'
                        ],
                    ])
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organizacion::class,
        ]);
    }
}
