<?php

namespace AppSistema\Form;

use AppSistema\Entity\Rol;
use AppSistema\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UsuarioType extends AbstractType
{

    private $manager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->manager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Identificador',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                    'data-autocomplete' => $options['usuarios'] ?: false,
                    'data-validate' => 'required pattern=(^(?=.{4,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$)'
                ]
            ])
            ->add('fotoFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_link' => false,
                'label' => 'Avatar',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'file',
                    'data-mode' => 'drop',
                    'data-button-title' => 'Seleccione el avatar del usuario'
                ],
            ])
            ->add('activo', CheckboxType::class, [
                'label' => '¿Está activo?',
                'required' => false,
                'attr' => [
                    'data-role' => 'switch',
                ],
            ])
            ->add('roles', EntityType::class, [
                'class' => Rol::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'select',
                    'data-validate' => 'required'
                ]
            ])
            ->add('nombres', TextType::class, [
                'attr' => [
                    'label' => 'Nombre(s)',
                    'class' => 'metro-input',
                    'data-validate' => 'required'
                ]
            ])
            ->add('apellidos', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                ]
            ])
            ->add('cargo', TextType::class, [
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                    'data-validate' => 'required',
                    'data-autocomplete' => $options['cargos'] ?: false,
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Contraseña',
                'empty_data' => '',
                'attr' => [
                    'data-role' => 'input',
                    'class' => 'metro-input',
                    'data-validate' => $options['password_required'] ? 'required' : ''
                ],
                'required' => $options['password_required'],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmar contraseña',
                'empty_data' => '',
                'attr' => [
                    'data-role' => 'input',
                    'class' => 'metro-input',
                    'data-validate' => ($options['password_required'] ? 'required' : '') . ' compare=usuario[password]'
                ],
                'required' => $options['password_required'],
                'mapped' => false
            ])
        ;

        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($transform) {
                    $roles = [];
                    foreach ($transform as $rol_identificador){
                        $roles[] = $this->manager->getRepository(Rol::class)->findOneByIdentificador($rol_identificador);
                    }
                    return $roles;
                },
                function ($reverseTransform) {
                    $ids = [];
                    foreach ($reverseTransform as $rol){
                        $ids[] = $rol->getIdentificador();
                    }
                    return $ids;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
            'password_required' => true,
            'cargos' => false,
            'usuarios' => false,
        ]);
    }
}
