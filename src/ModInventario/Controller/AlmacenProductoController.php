<?php

namespace ModInventario\Controller;

use App\Controller\AppController;
use ModInventario\Entity\Almacen;
use ModInventario\Repository\AlmacenProductoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlmacenProductoController extends AppController
{
    /**
     * @Route("/almacen_producto", name="app_almacen_producto_index", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function index(Request $request, AlmacenProductoRepository $almacenProductoRepository): Response
    {
        $form = $this->createFormBuilder()
            ->add('almacen', EntityType::class, [
                'class' => Almacen::class,
                'choice_label' => 'nombre',
                'label' => false,
                'placeholder' => 'Todos',
                'attr' => [
                    'data-role' => 'select',
                    'class' => 'metro-input',
                    'data-prepend' => 'Productos a mostrar',
                    'data-on-change' => 'submit_form()'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        $almacenesProductos = $almacenProductoRepository->findAll();

        $almacen = null;

        if($form->isSubmitted() && $form->isValid()){
            $almacen = $form->getData()['almacen'];
            if($almacen){
                $almacenesProductos = $almacenProductoRepository->findBy(['almacen' => $almacen]);
            }

        }

        if(!$almacen){
            $this->notificacionSatisfactoria('Mostrando los productos de TODOS los almacenes');
        } else {
            $this->notificacionSatisfactoria('Mostrando los productos del almacén: ' . $almacen->getNombre());
        }

        return $this->renderForm('almacen_producto/index.html.twig', [
            'almacenesProductos' => $almacenesProductos,
            'almacen_seleccionado' => $almacen,
            'almacen_seleccion_form' => $form
        ]);
    }
}
