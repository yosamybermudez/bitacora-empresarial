<?php

namespace ModInventario\Controller;

use App\Controller\AppController;
use ModInventario\Entity\Producto;
use ModInventario\Form\ProductoType;
use ModInventario\Repository\AlmacenRepository;
use ModInventario\Repository\ProductoRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/producto")
 */
class ProductoController extends AppController
{
    /**
     * @Route("/", name="app_producto_index", methods={"GET"})
     */
    public function index(Request $request, ProductoRepository $productoRepository, AlmacenRepository $almacenRepository): Response
    {

        $productos = $productoRepository->findAll();

        return $this->renderForm('producto/index.html.twig', [
            'productos' => $productos
        ]);
    }

    /**
     * @Route("/registrar", name="app_producto_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProductoRepository $productoRepository): Response
    {
        $producto = new Producto();
        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $productoRepository->add($producto, $this->getUser(), true);
            } catch (UniqueConstraintViolationException $e){
                $this->notificacionError('El producto ' . $producto->getNombre() . ' ya está registrado');
                return $this->renderForm('producto/new.html.twig', [
                    'producto' => $producto,
                    'form' => $form,
                ]);
            }

            $this->notificacionSatisfactoria('Producto registrado satisfactoriamente');
            return $this->redirectToRoute('app_producto_show', ['id' => $producto->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('producto/new.html.twig', [
            'producto' => $producto,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/duplicate", name="app_producto_duplicate", methods={"GET", "POST"})
     */
    public function duplicate(Request $request, ProductoRepository $productoRepository, Producto $producto): Response
    {
        $duplicate = clone $producto;
        $duplicate->setNombre($producto->getNombre() . " duplicado");
        $duplicate->setId(null);
        $form = $this->createForm(ProductoType::class, $duplicate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $productoRepository->add($duplicate, $this->getUser(),true);
            } catch (UniqueConstraintViolationException $e){
                $this->notificacionError('El producto ' . $duplicate->getNombre() . ' ya está registrado');
                return $this->renderForm('producto/new.html.twig', [
                    'producto' => $producto,
                    'form' => $form,
                ]);
            }

            $this->notificacionSatisfactoria('Producto duplicado satisfactoriamente');
            return $this->redirectToRoute('app_producto_show', ['id' => $duplicate->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('producto/new.html.twig', [
            'producto' => $producto,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_producto_show", methods={"GET","POST"})
     */
    public function show(Request $request, Producto $producto): Response
    {

        $movimientos = [];
        foreach ($producto->getProductoMovimientos() as $productoMovimiento) {
            $movimiento['id'] = $productoMovimiento->getMovimiento()->getId();
            $movimiento['creadoEn'] = $productoMovimiento->getMovimiento()->getCreadoEn();
            $movimiento['creadoPor'] = $productoMovimiento->getMovimiento()->getCreadoPor();
            $movimiento['codigo'] = $productoMovimiento->getMovimiento()->getCodigo();
            $movimiento['cantidad'] = $productoMovimiento->getCantidad();
            $movimiento['estado'] = $productoMovimiento->getMovimiento()->getEstado();
            $movimientos[] = $movimiento;
        }

        foreach ($producto->getAlmacenProductos() as $almacenProducto){
            foreach ($almacenProducto->getAlmacenProductoMovimientos() as $almacenProductoMovimiento){
                $movimiento['id'] = $almacenProductoMovimiento->getMovimiento()->getId();
                $movimiento['creadoEn'] = $almacenProductoMovimiento->getMovimiento()->getCreadoEn();
                $movimiento['creadoPor'] = $almacenProductoMovimiento->getMovimiento()->getCreadoPor();
                $movimiento['codigo'] = $almacenProductoMovimiento->getMovimiento()->getCodigo();
                $movimiento['cantidad'] = $almacenProductoMovimiento->getCantidad();
                $movimiento['estado'] = $almacenProductoMovimiento->getMovimiento()->getEstado();
                $movimientos[] = $movimiento;
            }
        }

        $form = $this->createFormBuilder()
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'nombre',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.nombre', 'ASC');
                },
                'label' => 'Producto a mostrar',
                'placeholder' => 'Seleccione',
                'data' => $producto,
                'attr' => [
                    'data-role' => 'select',
                    'class' => 'metro-input',
                    'data-prepend' => 'Producto',
                    'data-on-change' => 'submit_form()'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $producto = $form->getData()['producto'];
            return $this->redirectToRoute('app_producto_show', ['id' => $producto->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('producto/show.html.twig', [
            'producto' => $producto,
            'form' => $form->createView(),
            'movimientos' => $movimientos
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_producto_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Producto $producto, ProductoRepository $productoRepository): Response
    {
        $form = $this->createForm(ProductoType::class, $producto);
        $form->remove('existencia');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $productoRepository->add($producto, $this->getUser(),true);
            } catch (UniqueConstraintViolationException $e){
                $this->notificacionError('El producto ' . $producto->getNombre() . ' ya está registrado');
                return $this->renderForm('producto/new.html.twig', [
                    'producto' => $producto,
                    'form' => $form,
                ]);
            }

            $this->notificacionSatisfactoria('Producto modificado satisfactoriamente');
            return $this->redirectToRoute('app_producto_show', ['id' => $producto->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('producto/new.html.twig', [
            'producto' => $producto,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/toggle", name="app_producto_toggle", methods={"GET"})
     */
    public function toggle(Producto $producto, ProductoRepository $productoRepository): Response
    {
        $producto->setActivo(!$producto->isActivo());
        $productoRepository->add($producto, $this->getUser(), true);
        $accion = $producto->isActivo() ? 'habilitado' : 'inhabilitado';
        $this->notificacionSatisfactoria('Producto ' . $accion . ' satisfactoriamente');
        return $this->redirectToRoute('app_producto_index', [], Response::HTTP_SEE_OTHER);
    }
}
