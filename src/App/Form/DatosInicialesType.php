<?php

namespace App\Form;

use App\Entity\Almacen;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class DatosInicialesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add(
                $builder->create('bd_conexion', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => false
                ])

                    ->add('bd_nombre', TextType::class, [
                        'label' => 'Nombre',
                        'attr' => [
                            'class' => 'metro-input',
                            'readonly' => 'readonly',
                        ],
                        'data' => $options['dbname']
                    ])
                    ->add('bd_servidor', TextType::class, [
                        'label' => 'Servidor',
                        'attr' => [
                            'class' => 'metro-input',
                            'readonly' => 'readonly',
                        ],
                        'data' => $options['host']
                    ])
                    ->add('bd_puerto', TextType::class, [
                        'label' => 'Puerto',
                        'data' => '3306',
                        'attr' => [
                            'class' => 'metro-input',
                            'readonly' => 'readonly',
                        ],
                        'data' => $options['port']
                    ])
            )
            ->add(
                $builder->create('organizacion', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => false
                ])
                    ->add('organizacion_nombre', TextType::class, [
                        'label' => 'Nombre',
                        'required' => true,
                        'attr' => [
                            'class' => 'metro-input',
                            'data-validate' => 'required'
                        ],
                    ])
                    ->add('organizacion_siglas', TextType::class, [
                        'label' => 'Siglas',
                        'required' => true,
                        'attr' => [
                            'class' => 'metro-input',
                        ],
                    ])

            )
            ->add(
                $builder->create('super_admin', FormType::class, [
                    'mapped' => false,
                    'inherit_data' => true,
                    'label' => false
                ])
                    ->add('username', TextType::class, [
                        'label' => 'Identificador',
                        'required' => true,
                        'attr' => [
                            'class' => 'metro-input',
                            'data-validate' => 'required'
                        ],
                    ])
                    ->add('password', PasswordType::class, [
                        'label' => 'Contraseña',
                        'required' => true,
                        'attr' => [
                            'class' => 'metro-input',
                            'data-validate' => 'required',
                            'data-role' => 'input'
                        ],
                    ])
                    ->add('repeat_password', PasswordType::class, [
                        'label' => 'Repetir contraseña',
                        'attr' => [
                            'class' => 'metro-input',
                            'data-validate' => 'required compare=datos_iniciales[super_admin][password]',
                            'data-role' => 'input'
                        ],
                    ])
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'dbname' => null,
            'host' => null,
            'port' => null,
        ]);
    }
}
