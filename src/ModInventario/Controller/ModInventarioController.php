<?php

namespace ModInventario\Controller;

use App\Controller\AppController;
use ModInventario\Entity\Almacen;
use ModInventario\Entity\AlmacenProducto;
use ModInventario\Entity\AlmacenProductoMovimiento;
use ModInventario\Entity\Movimiento;
use ModInventario\Entity\MovimientoEstado;
use ModInventario\Repository\MovimientoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/inventario")
 */
class ModInventarioController extends AppController
{
    /**
     * @Route("/", name="app_mod_inventario_dashboard")
     */
    public function dashboard(Request $request, MovimientoRepository $movimientoRepository): Response
    {
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                return $this->redirectToRoute('app_mod_inventario_dashboard');
            }
        }

        $entradaCompras = $movimientoRepository->findMovimientosFecha('entradaCompras', $fecha);
        $entradaDevoluciones = $movimientoRepository->findMovimientosFecha('entradaDevoluciones', $fecha);

        $salidaVentas = $movimientoRepository->findMovimientosFecha('salidaVentas', $fecha);
        $salidaGastosAporte = $movimientoRepository->findMovimientosFecha('salidaGastosAporte', $fecha);
        $salidaTransferenciasAlmacen = $movimientoRepository->findMovimientosFecha('salidaTransferenciaAlmacen', $fecha);
        $salidaDevoluciones = $movimientoRepository->findMovimientosFecha('salidaDevoluciones', $fecha);

        $tiposMovimiento = [
            ['id' => 'entradaCompras', 'nombre' => 'Devoluciones a clientes', 'mifIcon' => 'shopping-cart', 'data' => $entradas, 'enlace' => $this->generateUrl('app_mod_ventas_devoluciones_list')],
            ['id' => 'entradaDevoluciones', 'nombre' => 'Devoluciones de clientes', 'mifIcon' => 'enter', 'data' => $devolucionesProveedores, 'enlace' => $this->generateUrl('app_mod_ventas_devoluciones_list')],
            ['id' => 'salidaVentas', 'nombre' => 'Ventas', 'mifIcon' => 'cart', 'data' => $ventas, 'enlace' => $this->generateUrl('app_mod_ventas_ventas_list')],
            ['id' => 'salidaGastosAporte', 'nombre' => 'Gastos de aporte', 'mifIcon' => 'gift', 'data' => $gastosDeAporte, 'enlace' => $this->generateUrl('app_mod_ventas_gastos_de_aporte_list')],
            ['id' => 'salidasDevoluciones', 'nombre' => 'Devoluciones de proveedores', 'mifIcon' => 'exit', 'data' => $devolucionesClientes, 'enlace' => $this->generateUrl('app_mod_ventas_devoluciones_list')],
        ];

        return $this->render('mod_ventas/dashboard.html.twig', [
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tiposMovimiento' => $tiposMovimiento
        ]);
    }

    /**
     * @Route("/transferencia_almacen/registrar", name="app_movimiento_transferencia_almacen_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarTransferenciaAlmacen(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Debe existir mas de un almacen para poder hacer la transferencia
        $almacenes = $entityManager->getRepository(Almacen::class)->findAll();
        if(!$almacenes || count($almacenes) === 1){
            $this->notificacionError('No hay almacenes suficientes para realizar trasnferencias entre ellos. Registre un nuevo almacén para poder vincularlo');
            return $this->redirectToRoute('app_almacen_new', [], Response::HTTP_SEE_OTHER);
        }
        //Solo se puede realizar esta accion si existen productos en los almacenes
        $almacen_productos = $entityManager->getRepository(AlmacenProducto::class)->findExistenciasDisponibles();
        if(!$almacen_productos){
            $this->notificacionError('No puede realizar esta acción cuando no existen productos en almacén');
            return $this->redirectToRoute('app_movimiento_index', [], Response::HTTP_SEE_OTHER);
        }
        $movimientoRequest = new MovimientoTransferenciaAlmacenRequest();
        $movimientoRequest->addAlmacenProductoMovimiento(new AlmacenProductoMovimiento());
        $movimientoRequest->descripcion = 'Transferencia entre almacenes';
        $movimientoRequest->fecha = date('d-m-Y');


        $form = $this->createForm(MovimientoTransferenciaAlmacenType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Obteniendo los datos de la petición
            $fecha = $movimientoRequest->fecha;
            $descripcion = $movimientoRequest->descripcion;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;
            $almacen_destino = $movimientoRequest->almacen_destino;
            $cantidad = count($almacen_producto_movimientos);
            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento) {
                $almacen_producto = $almacen_producto_movimiento->getAlmacenProducto();
                $isProductoInAlmacenDestino = $entityManager->getRepository(AlmacenProducto::class)->findOneBy(['producto' => $almacen_producto->getProducto(), 'almacen' => $almacen_destino]);
                if ($isProductoInAlmacenDestino)
                    $cantidad--;
            }

            if($cantidad === 0){
                $this->notificacionError('El almacén de origen y destino de todos los productos es el mismo');
                $form = $this->createForm(MovimientoTransferenciaAlmacenType::class, $movimientoRequest);
                return $this->renderForm('movimiento/transferencia_almacen.html.twig', [
                    'form' => $form,
                ]);
            }

            // Generando un nuevo codigo
            $codigo = 'TRF' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoTransferenciaAlmacen::class)->findAll()) + 1) ;

            // Creando el objeto movimiento con los datos
            $movimiento = $entityManager->getRepository(Movimiento::class)->findOneByCodigo($codigo) ?: new Movimiento();

            $movimiento->setEstado('Sin confirmar');
            $movimiento->setFecha(new \DateTime($fecha));
            $movimiento->setDescripcion($descripcion ?: 'Transferencia entre almacenes');
            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Transferencias (almacenes)');

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Creando un nuevo estado para el movimiento
            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);

            //
            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $almacen_producto = $almacen_producto_movimiento->getAlmacenProducto();
                $isProductoInAlmacenDestino = $entityManager->getRepository(AlmacenProducto::class)->findOneBy(['producto' => $almacen_producto->getProducto(), 'almacen' => $almacen_destino]);
                if($isProductoInAlmacenDestino)
                    continue;
//                $almacen_producto->setSaldoDisponible($almacen_producto->getSaldoDisponible() - $almacen_producto_movimiento->getCantidad());
                $entityManager->getRepository(AlmacenProducto::class)->add($almacen_producto, $this->getUser(), true);

                //
                $movimiento->addAlmacenProductoMovimiento($almacen_producto_movimiento);

                // Asociar el Movimiento al AlmacenProducto
                $almacen_producto_movimiento->setMovimiento($movimiento);
                $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacen_producto_movimiento, $this->getUser(), true);
            }

            $movimiento->setImporteTotalVigenteCup($importeTotalVigenteCup);
            $movimiento->setImporteTotalVigenteMlc($importeTotalVigenteMlc);

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Registrar el movimiento creado, como una Transferencia entre almacenes
            $movimientoTransferenciaAlmacen = new MovimientoTransferenciaAlmacen();
            $movimientoTransferenciaAlmacen->setMovimiento($movimiento);
            $movimientoTransferenciaAlmacen->setAlmacen($almacen_destino);
            $entityManager->getRepository(MovimientoTransferenciaAlmacen::class)->add($movimientoTransferenciaAlmacen, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Transferencia entre almacenes ' . $movimiento->getCodigo() . ' registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_transferencia_almacen_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Transferencia entre almacenes ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_transferencia_almacen_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Transferencia entre almacenes ' . $movimiento->getCodigo() . '  registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Transferencia entre almacenes ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('movimiento/transferencia_almacen.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/ajuste_inventario/registrar", name="app_movimiento_ajuste_inventario_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarAjusteInventario(Request $request, EntityManagerInterface $entityManager): Response
    {
        $movimientosSinConfirmar = $entityManager->getRepository(Movimiento::class)->findByEstado('Sin confirmar');
        if($movimientosSinConfirmar && count($movimientosSinConfirmar) > 0){
            $this->notificacionError('Para realizar un ajuste de inventario debe confirmar o cancelar los movimientos registrados anteriormente');
            return $this->redirectToRoute('app_movimiento_unconfirmed');
        }
        //Solo se puede realizar esta accion si existen productos en los almacenes
        $almacen_productos = $entityManager->getRepository(AlmacenProducto::class)->findExistenciasDisponibles();
        if(!$almacen_productos){
            $this->notificacionError('No puede realizar esta acción cuando no existen productos en almacén');
            return $this->redirectToRoute('app_movimiento_index', [], Response::HTTP_SEE_OTHER);
        }
        $movimientoRequest = new MovimientoAjusteInventarioRequest();
        $movimientoRequest->addAlmacenProductoMovimiento(new AlmacenProductoMovimiento());
        $movimientoRequest->descripcion = 'Ajuste de inventario de productos';
        $movimientoRequest->fecha = date('d-m-Y');


        $form = $this->createForm(MovimientoAjusteInventarioType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Obteniendo los datos de la petición
            $fecha = $movimientoRequest->fecha;
            $descripcion = $movimientoRequest->descripcion;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;

            // Generando un nuevo codigo
            $codigo = 'AJT' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoAjusteInventario::class)->findAll()) + 1) ;


            // Creando el objeto movimiento con los datos
            $movimiento = $entityManager->getRepository(Movimiento::class)->findOneByCodigo($codigo) ?: new Movimiento();

            $movimiento->setEstado('Sin confirmar');
            $movimiento->setFecha(new \DateTime($fecha));
            $movimiento->setDescripcion($descripcion ?: 'Ajuste de inventario');
            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Ajuste de inventario');

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Creando un nuevo estado para el movimiento
            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);

            //
            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $almacen_producto = $almacen_producto_movimiento->getAlmacenProducto();
                $entityManager->getRepository(AlmacenProducto::class)->add($almacen_producto, $this->getUser(), true);

                //
                $movimiento->addAlmacenProductoMovimiento($almacen_producto_movimiento);

                // Asociar el Movimiento al AlmacenProducto
                $almacen_producto_movimiento->setMovimiento($movimiento);
                $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacen_producto_movimiento, $this->getUser(), true);
            }

            $movimiento->setImporteTotalVigenteCup($importeTotalVigenteCup);
            $movimiento->setImporteTotalVigenteMlc($importeTotalVigenteMlc);

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Registrar el movimiento creado, como un Ajuste de inventario
            $movimientoAjusteInventario = new MovimientoAjusteInventario();
            $movimientoAjusteInventario->setMovimiento($movimiento);
            $movimientoAjusteInventario->setMotivo($descripcion ?: 'N/E');
            $entityManager->getRepository(MovimientoAjusteInventario::class)->add($movimientoAjusteInventario, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Ajuste de inventario ' . $movimiento->getCodigo() . ' registrado y confirmado satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_ajuste_inventario_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Ajuste de inventario ' . $movimiento->getCodigo() . ' registrado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_ajuste_inventario_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Ajuste de inventario ' . $movimiento->getCodigo() . '  registrado y confirmado satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Ajuste de inventario ' . $movimiento->getCodigo() . ' registrado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            //
        }

        return $this->renderForm('movimiento/ajuste_inventario.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/transferencia/edit", name="app_movimiento_transferencia_almacen_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarTransferenciaAlmacen(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {
        $oldMovimiento = clone $movimiento;
        $oldMovimientoTransferenciaAlmacen = clone $movimiento->getMovimientoTransferenciaAlmacen();

        $almacenProductoMovimientos = $movimiento->getAlmacenProductoMovimientos();
        $movimientoRequest = new MovimientoTransferenciaAlmacenRequest();
        $movimientoRequest->descripcion = $movimiento->getDescripcion();
        $movimientoRequest->almacen_destino = count($movimiento->getAlmacenProductoMovimientos()) > 0 ? $movimiento->getAlmacenProductoMovimientos()[0]->getAlmacenProducto()->getAlmacen() : null;

        foreach($almacenProductoMovimientos as $almacenProductoMovimiento) {
            $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
            $almacenProducto->setSaldoDisponible($almacenProducto->getSaldoContable());
            $movimientoRequest->addAlmacenProductoMovimiento($almacenProductoMovimiento);
        }

        $form = $this->createForm(MovimientoTransferenciaAlmacenType::class, $movimientoRequest);
        $form->remove('fecha');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $requestAlmacenDestino = $movimientoRequest->almacen_destino;
            $requestAlmacenProductoMovimientos = $movimientoRequest->almacen_producto_movimientos;

            foreach($movimiento->getAlmacenProductoMovimientos()->toArray() as $almacenProductoMovimiento){
                $movimiento->removeAlmacenProductoMovimiento($almacenProductoMovimiento);
            }
            //
            $movimiento->setDescripcion($movimientoRequest->descripcion ?: 'Transferencia entre almacenes');
            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $movimientoTransferenciaAlmacen = $movimiento->getMovimientoTransferenciaAlmacen();
            $movimientoTransferenciaAlmacen->setAlmacen($requestAlmacenDestino);
            $entityManager->getRepository(MovimientoTransferenciaAlmacen::class)->add($movimientoTransferenciaAlmacen, true);

            foreach ($requestAlmacenProductoMovimientos as $almacenProductoMovimiento){
                $almacenProductoMovimiento->setMovimiento($movimiento);
                $movimiento->addAlmacenProductoMovimiento($almacenProductoMovimiento);
                $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacenProductoMovimiento, $this->getUser(), true);
            }

            $this->notificacionSatisfactoria('Transferencia entre almacenes modificada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoTransferenciaAlmacen);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/transferencia_almacen.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @Route("/{id}/ajuste_inventario/edit", name="app_movimiento_ajuste_inventario_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarAjusteInventario(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {
        $oldMovimiento = clone $movimiento;
        $oldMovimientoAjusteInventario = clone $movimiento->getMovimientoAjusteInventario();

        $almacenProductoMovimientos = $movimiento->getAlmacenProductoMovimientos();
        $movimientoRequest = new MovimientoAjusteInventarioRequest();
        $movimientoRequest->descripcion = $movimiento->getDescripcion();
        $movimientoRequest->fecha = $movimiento->getFecha();

        foreach($almacenProductoMovimientos as $almacenProductoMovimiento) {
            $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
            $almacenProducto->setSaldoDisponible($almacenProducto->getSaldoContable());
            $movimientoRequest->addAlmacenProductoMovimiento($almacenProductoMovimiento);
        }

        $form = $this->createForm(MovimientoAjusteInventarioType::class, $movimientoRequest);
        $form->remove('fecha');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestAlmacenProductoMovimientos = $movimientoRequest->almacen_producto_movimientos;
            $requestMotivo = $movimientoRequest->motivo;

            foreach($movimiento->getAlmacenProductoMovimientos()->toArray() as $almacenProductoMovimiento){
                $movimiento->removeAlmacenProductoMovimiento($almacenProductoMovimiento);
            }
            //
            $movimiento->setDescripcion($movimientoRequest->descripcion ?: 'Ajuste de inventario');
            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $movimientoAjusteInventario = $movimiento->getMovimientoAjusteInventario();
            $movimientoAjusteInventario->setMotivo($requestMotivo);
            $entityManager->getRepository(MovimientoAjusteInventario::class)->add($movimientoAjusteInventario, $this->getUser(),true);

            foreach ($requestAlmacenProductoMovimientos as $almacenProductoMovimiento){
                $almacenProductoMovimiento->setMovimiento($movimiento);
                $movimiento->addAlmacenProductoMovimiento($almacenProductoMovimiento);
                $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacenProductoMovimiento, $this->getUser(), true);
            }

            $this->notificacionSatisfactoria('Ajuste de inventario modificado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoAjusteInventario);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/ajuste_inventario.html.twig', [
            'form' => $form
        ]);
    }
}
