<?php

namespace ModInventario\Form;

use ModInventario\Entity\Almacen;
use ModInventario\Entity\Organizacion;
use ModInventario\Request\MovimientoTransferenciaAlmacenRequest;
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

class MovimientoInternoTransferenciaAlmacenType extends AbstractType
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
                'placeholder' => 'Seleccione',
            ])
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
                    'precio' => 'compra',
                    'precio_inputs' => false
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MovimientoTransferenciaAlmacenRequest::class,
        ]);
    }
}
