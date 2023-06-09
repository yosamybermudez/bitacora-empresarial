<?php

namespace ModVentas\Controller;

use App\Controller\AppController;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ventas")
 */
class ModVentasController extends AppController
{
    /**
     * @Route("/", name="app_mod_ventas_dashboard")
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
                return $this->redirectToRoute('app_mod_ventas_dashboard');
            }
        }

        $ventas = $movimientoRepository->findMovimientosFecha('ventas', $fecha);
        $devoluciones = $movimientoRepository->findMovimientosFecha('devoluciones', $fecha);
        $gastosDeAporte = $movimientoRepository->findMovimientosFecha('gastosDeAporte', $fecha);

//        if(!in_array($this->tipo_movimiento, ['entradas', 'compras', 'ventas', 'devoluciones', 'retornos', 'gastosDeAporte', 'ajustes', 'transferencias']))
//        {
//            $this->notificacionError('Enlace no válido. Se redirecciona hacia el tablero del módulo Compras');
//            return $this->redirectToRoute('app_mod_ventas_dashboard');
//        }

        $tiposMovimiento = [
            ['id' => 'ventas', 'nombre' => 'Ventas', 'mifIcon' => 'cart', 'data' => $ventas, 'enlace' => $this->generateUrl('app_mod_ventas_ventas_list')],
            ['id' => 'gastosDeAporte', 'nombre' => 'Gastos de aporte', 'mifIcon' => 'gift', 'data' => $gastosDeAporte, 'enlace' => $this->generateUrl('app_mod_ventas_gastos_de_aporte_list')],
            ['id' => 'devoluciones', 'nombre' => 'Devoluciones', 'mifIcon' => 'exit', 'data' => $devoluciones, 'enlace' => $this->generateUrl('app_mod_ventas_devoluciones_list')],
        ];

        return $this->render('mod_ventas/dashboard.html.twig', [
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tiposMovimiento' => $tiposMovimiento
        ]);
    }

    /**
     * @Route("/ventas/listar", name="app_mod_ventas_ventas_list")
     */
    public function listVentas(Request $request, MovimientoRepository $movimientoRepository): Response
    {
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                return $this->redirectToRoute('app_mod_ventas_dashboard');
            }
        }
        $movimientos = $movimientoRepository->findMovimientosFecha('ventas', $fecha);
        return $this->render('mod_ventas/index.html.twig', [
            'movimientos' => $movimientos,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tipo' => 'Ventas',
            'siglas' => 'VEN'
        ]);
    }

    /**
     * @Route("/gastos_de_aporte/listar", name="app_mod_ventas_gastos_de_aporte_list")
     */
    public function listGastosDeAporte(Request $request, MovimientoRepository $movimientoRepository): Response
    {
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                return $this->redirectToRoute('app_mod_ventas_dashboard');
            }
        }
        $movimientos = $movimientoRepository->findMovimientosFecha('gastosDeAporte', $fecha);
        return $this->render('mod_ventas/index.html.twig', [
            'movimientos' => $movimientos,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tipo' => 'Gastos de aporte',
            'siglas' => 'GAP'
        ]);
    }

    /**
     * @Route("/devoluciones/listar", name="app_mod_ventas_devoluciones_list")
     */
    public function listDevoluciones(Request $request, MovimientoRepository $movimientoRepository): Response
    {
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                return $this->redirectToRoute('app_mod_ventas_dashboard');
            }
        }
        $movimientos = $movimientoRepository->findMovimientosFecha('devoluciones', $fecha);
        return $this->render('mod_ventas/index.html.twig', [
            'movimientos' => $movimientos,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tipo' => 'Devoluciones',
            'siglas' => 'DEV'
        ]);
    }

    /**
     * @Route("/venta/registrar", name="app_mod_ventas_venta_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarVenta(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Solo se puede realizar una venta si existen productos en los almacenes
        $almacen_productos = $entityManager->getRepository(AlmacenProducto::class)->findExistenciasDisponibles();
        if(!$almacen_productos){
            $this->notificacionError('No puede realizar una venta cuando no existen productos en almacén');
            return $this->redirectToRoute('app_mod_ventas_list', [], Response::HTTP_SEE_OTHER);
        }
        $movimientoRequest = new MovimientoVentaRequest();
        $movimientoRequest->addAlmacenProductoMovimiento(new AlmacenProductoMovimiento());
        $movimientoRequest->descripcion = 'Venta de productos al cliente';
        $movimientoRequest->fecha = date('d-m-Y');


        $form = $this->createForm(MovimientoVentaType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Obteniendo los datos de la petición
            $cliente = $movimientoRequest->cliente;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;
            $descripcion = $movimientoRequest->descripcion;
            $fecha = $movimientoRequest->fecha;

            // Generando un nuevo codigo
            $codigo = 'VEN' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoVenta::class)->findAll()) + 1) ;

            // Creando el objeto movimiento con los datos
            $movimiento = $entityManager->getRepository(Movimiento::class)->findOneByCodigo($codigo) ?: new Movimiento();

            //
            $movimiento->setEntregadoPorNombre($movimientoRequest->entregado_por_nombre);
            $movimiento->setEntregadoPorCargo($movimientoRequest->entregado_por_cargo);
            $movimiento->setEntregadoPorCI($movimientoRequest->entregado_por_ci);

            $movimiento->setTransportadoPorNombre($movimientoRequest->transportado_por_nombre);
            $movimiento->setTransportadoPorCargo($movimientoRequest->transportado_por_cargo);
            $movimiento->setTransportadoPorCI($movimientoRequest->transportado_por_ci);

            $movimiento->setRecibidoPorNombre($movimientoRequest->recibido_por_nombre);
            $movimiento->setRecibidoPorCargo($movimientoRequest->recibido_por_cargo);
            $movimiento->setRecibidoPorCI($movimientoRequest->recibido_por_ci);
            //

            $movimiento->setEstado('Sin confirmar');
            $movimiento->setFecha(new \DateTime($fecha));

            if($cliente){
                $movimiento->setEmpresa($cliente);
                $descripcion = sprintf('Venta de productos al cliente "%s"', $cliente->getNombre());
            } else {
                $descripcion = 'Venta de productos al cliente';
            }

            $movimiento->setDescripcion($descripcion);

            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Venta');


            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);


            // Creando un nuevo estado para el movimiento
            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);

            //
            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $importeTotalVigenteCup += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup();
                $importeTotalVigenteMlc += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc();
                // Una venta sin confirmar disminuye el saldo disponible de los productos
                // De cancelarla, se vuelve a aumentar el saldo disponible
                // De confirmarse, se disminuye el saldo contable
                $almacen_producto = $almacen_producto_movimiento->getAlmacenProducto();
                $almacen_producto->setSaldoDisponible($almacen_producto->getSaldoDisponible() - $almacen_producto_movimiento->getCantidad());
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

            // Registrar el movimiento creado, como una Venta de productos al cliente
            $movimientoVenta = new MovimientoVenta();
            $movimientoVenta->setMovimiento($movimiento);
            $entityManager->getRepository(MovimientoVenta::class)->add($movimientoVenta, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . ' registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_venta_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_venta_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . ' registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            //
        }

        return $this->renderForm('mod_ventas/new_venta.html.twig', [
            'form' => $form,
            'rapida' => false
        ]);
    }

    /**
     * @Route("/venta/rapida/registrar", name="app_mod_ventas_venta_rapida_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarVentaRapida(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Solo se puede realizar una venta si existen productos en los almacenes
        $almacen_productos = $entityManager->getRepository(AlmacenProducto::class)->findExistenciasDisponibles();
        if(!$almacen_productos){
            $this->notificacionError('No puede realizar una venta cuando no existen productos en almacén');
            return $this->redirectToRoute('app_mod_ventas_list', [], Response::HTTP_SEE_OTHER);
        }
        $movimientoRequest = new MovimientoVentaRapidaRequest();
        $movimientoRequest->addAlmacenProductoMovimiento(new AlmacenProductoMovimiento());

        // Generando un nuevo codigo
        $codigo = 'VEN' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoVenta::class)->findAll()) + 1) ;

        $movimientoRequest->codigo = $codigo;
        $form = $this->createForm(MovimientoVentaRapidaType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Obteniendo los datos de la petición
            $cliente = $movimientoRequest->cliente;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;

            // Creando el objeto movimiento con los datos
            $movimiento = $entityManager->getRepository(Movimiento::class)->findOneByCodigo($codigo) ?: new Movimiento();

            $movimiento->setEstado('Sin confirmar');
            $movimiento->setFecha(new \DateTime());


            if($cliente){
                $movimiento->setEmpresa($cliente);
                $descripcion = sprintf('Venta de productos al cliente "%s"', $cliente->getNombre());
            } else {
                $descripcion = 'Venta de productos al cliente';
            }

            $movimiento->setDescripcion($descripcion);

            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Venta');
            $movimiento->setLite(true);

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Creando un nuevo estado para el movimiento
            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);

            //
            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $importeTotalVigenteCup += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup();
                $importeTotalVigenteMlc += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc();
                // Una venta sin confirmar disminuye el saldo disponible de los productos
                // De cancelarla, se vuelve a aumentar el saldo disponible
                // De confirmarse, se disminuye el saldo contable
                $almacen_producto = $almacen_producto_movimiento->getAlmacenProducto();
                $almacen_producto->setSaldoDisponible($almacen_producto->getSaldoDisponible() - $almacen_producto_movimiento->getCantidad());
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

            // Registrar el movimiento creado, como una Venta de productos al cliente
            $movimientoVenta = new MovimientoVenta();
            $movimientoVenta->setMovimiento($movimiento);
            $entityManager->getRepository(MovimientoVenta::class)->add($movimientoVenta, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . ' registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_venta_rapida_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_venta_rapida_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . '  registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Venta de productos ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            //
        }

        return $this->renderForm('movimiento/venta.html.twig', [
            'form' => $form,
            'rapida' => true
        ]);
    }

    /**
     * @Route("/devolucion/registrar", name="app_mod_ventas_devolucion_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarDevolucion(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Solo se puede realizar una devolucion si existen productos en los almacenes
        $almacen_productos = $entityManager->getRepository(AlmacenProducto::class)->findExistenciasDisponibles();
        if(!$almacen_productos){
            $this->notificacionError('No puede realizar una devolución cuando no existen productos en almacén');
            return $this->redirectToRoute('app_movimiento_index', [], Response::HTTP_SEE_OTHER);
        }
        $movimientoDevolucionRequest = new MovimientoDevolucionRequest();
        $movimientoDevolucionRequest->addAlmacenProductoMovimiento(new AlmacenProductoMovimiento());
        $movimientoDevolucionRequest->descripcion = 'Devolución de productos al proveedor';

        $form = $this->createForm(MovimientoDevolucionType::class, $movimientoDevolucionRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Obteniendo los datos de la petición
            $proveedor = $movimientoDevolucionRequest->proveedor;
            $almacen_producto_movimientos = $movimientoDevolucionRequest->almacen_producto_movimientos;
            $descripcion = $movimientoDevolucionRequest->descripcion;
            $fecha = $movimientoDevolucionRequest->fecha;

            // Generando un nuevo codigo
            $codigo = 'DEV' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoDevolucion::class)->findAll()) + 1) ;

            // Creando el objeto movimiento con los datos
            $movimiento = new Movimiento();

            //
            $movimientoRequest = $movimientoDevolucionRequest;
            $movimiento->setEntregadoPorNombre($movimientoRequest->entregado_por_nombre);
            $movimiento->setEntregadoPorCargo($movimientoRequest->entregado_por_cargo);
            $movimiento->setEntregadoPorCI($movimientoRequest->entregado_por_ci);

            $movimiento->setTransportadoPorNombre($movimientoRequest->transportado_por_nombre);
            $movimiento->setTransportadoPorCargo($movimientoRequest->transportado_por_cargo);
            $movimiento->setTransportadoPorCI($movimientoRequest->transportado_por_ci);

            $movimiento->setRecibidoPorNombre($movimientoRequest->recibido_por_nombre);
            $movimiento->setRecibidoPorCargo($movimientoRequest->recibido_por_cargo);
            $movimiento->setRecibidoPorCI($movimientoRequest->recibido_por_ci);
            //

            $movimiento->setFecha(new \DateTime($fecha));
            $movimiento->setEstado('Sin confirmar');
            $movimiento->setEmpresa($proveedor);
            $movimiento->setDescripcion($descripcion ?: sprintf('Devolución de productos al proveedor "%s"', $proveedor->getNombre()));
            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Devolución');

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Creando un nuevo estado para el movimiento
            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);

            //
            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $importeTotalVigenteCup += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioCompraCup();
                $importeTotalVigenteMlc += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioCompraMlc();
                // Una devolucion sin confirmar disminuye el saldo disponible de los productos
                // De cancelarla, se vuelve a aumentar el saldo disponible
                // De confirmarse, se disminuye el saldo contable
                $almacen_producto = $almacen_producto_movimiento->getAlmacenProducto();
                $almacen_producto->setSaldoDisponible($almacen_producto->getSaldoDisponible() - $almacen_producto_movimiento->getCantidad());
                $entityManager->getRepository(AlmacenProducto::class)->add($almacen_producto, $this->getUser(), true);

                //
                $movimiento->addAlmacenProductoMovimiento($almacen_producto_movimiento);

                // Asociar el Movimiento al AlmacenProducto
                $almacen_producto_movimiento->setMovimiento($movimiento);
                $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacen_producto_movimiento, $this->getUser(), true);
            }

            $movimiento->setImporteTotalVigenteCup($importeTotalVigenteCup);
            $movimiento->setImporteTotalVigenteMlc($importeTotalVigenteMlc);

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(), true);

            // Registrar el movimiento creado, como una Devolucion de productos al proveedor
            $movimientoDevolucion = new MovimientoDevolucion();
            $movimientoDevolucion->setMovimiento($movimiento);
            $entityManager->getRepository(MovimientoDevolucion::class)->add($movimientoDevolucion, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Devolución de productos (a proveedor) ' . $movimiento->getCodigo() . ' registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_devolucion_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Devolución de productos (a proveedor) ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_devolucion_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Devolución de productos (a proveedor) ' . $movimiento->getCodigo() . '  registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Devolución de productos (a proveedor) ' . $movimiento->getCodigo() . ' registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            //
        }

        return $this->renderForm('movimiento/devolucion.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/gasto_aporte/registrar", name="app_mod_ventas_gasto_aporte_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarGastoAporte(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Solo se puede realizar esta accion si existen productos en los almacenes
        $almacen_productos = $entityManager->getRepository(AlmacenProducto::class)->findExistenciasDisponibles();
        if(!$almacen_productos){
            $this->notificacionError('No puede realizar esta acción cuando no existen productos en almacén');
            return $this->redirectToRoute('app_movimiento_index', [], Response::HTTP_SEE_OTHER);
        }
        $movimientoRequest = new MovimientoGastoAporteRequest();
        $movimientoRequest->addAlmacenProductoMovimiento(new AlmacenProductoMovimiento());
        $movimientoRequest->descripcion = 'Gasto de aporte (regalo)';
        $movimientoRequest->fecha = date('d-m-Y');


        $form = $this->createForm(MovimientoGastoAporteType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Obteniendo los datos de la petición
            $cliente = $movimientoRequest->cliente;
            $fecha = $movimientoRequest->fecha;
            $descripcion = $movimientoRequest->descripcion;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;

            // Generando un nuevo codigo
            $codigo = 'GAP' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoGastoAporte::class)->findAll()) + 1) ;


            // Creando el objeto movimiento con los datos
            $movimiento = $entityManager->getRepository(Movimiento::class)->findOneByCodigo($codigo) ?: new Movimiento();

            //
            $movimiento->setEntregadoPorNombre($movimientoRequest->entregado_por_nombre);
            $movimiento->setEntregadoPorCargo($movimientoRequest->entregado_por_cargo);
            $movimiento->setEntregadoPorCI($movimientoRequest->entregado_por_ci);

            $movimiento->setTransportadoPorNombre($movimientoRequest->transportado_por_nombre);
            $movimiento->setTransportadoPorCargo($movimientoRequest->transportado_por_cargo);
            $movimiento->setTransportadoPorCI($movimientoRequest->transportado_por_ci);

            $movimiento->setRecibidoPorNombre($movimientoRequest->recibido_por_nombre);
            $movimiento->setRecibidoPorCargo($movimientoRequest->recibido_por_cargo);
            $movimiento->setRecibidoPorCI($movimientoRequest->recibido_por_ci);
            //

            $movimiento->setEstado('Sin confirmar');
            $movimiento->setFecha(new \DateTime($fecha));
            $movimiento->setEmpresa($cliente);
            $movimiento->setDescripcion($descripcion ?: 'Gasto de aporte');
            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Gasto de aporte');

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Creando un nuevo estado para el movimiento
            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);

            //
            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                /*
                // $importeTotalVigenteCup += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup();
                // $importeTotalVigenteMlc += $almacen_producto_movimiento->getCantidad() * $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc();
                // Una venta sin confirmar disminuye el saldo disponible de los productos
                // De cancelarla, se vuelve a aumentar el saldo disponible
                // De confirmarse, se disminuye el saldo contable
                */
                $almacen_producto = $almacen_producto_movimiento->getAlmacenProducto();
                $almacen_producto->setSaldoDisponible($almacen_producto->getSaldoDisponible() - $almacen_producto_movimiento->getCantidad());
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

            // Registrar el movimiento creado, como un Gasto de aporte (regalo al cliente)
            $movimientoGastoAporte = new MovimientoGastoAporte();
            $movimientoGastoAporte->setMovimiento($movimiento);
            $entityManager->getRepository(MovimientoGastoAporte::class)->add($movimientoGastoAporte, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Gasto de aporte (regalo) ' . $movimiento->getCodigo() . ' registrado y confirmado satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_gasto_aporte_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Gasto de aporte (regalo) ' . $movimiento->getCodigo() . ' registrado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_gasto_aporte_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Gasto de aporte (regalo) ' . $movimiento->getCodigo() . '  registrado y confirmado satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Gasto de aporte (regalo) ' . $movimiento->getCodigo() . ' registrado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            //
        }

        return $this->renderForm('movimiento/gasto_aporte.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/venta/edit", name="app_mod_ventas_venta_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarVenta(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {
        $oldMovimiento = clone $movimiento;
        $oldMovimientoVenta = clone $movimiento->getMovimientoVenta();

        $almacenProductoMovimientos = $movimiento->getAlmacenProductoMovimientos();

        $movimientoRequest = new MovimientoVentaRequest();

        $movimientoRequest->cliente = $movimiento->getEmpresa();
        $movimientoRequest->descripcion = $movimiento->getDescripcion();

        $movimientoRequest->entregado_por_nombre = $movimiento->getEntregadoPorNombre();
        $movimientoRequest->entregado_por_cargo = $movimiento->getEntregadoPorCargo();
        $movimientoRequest->entregado_por_ci = $movimiento->getEntregadoPorCI();

        $movimientoRequest->transportado_por_nombre = $movimiento->getTransportadoPorNombre();
        $movimientoRequest->transportado_por_cargo = $movimiento->getTransportadoPorCargo();
        $movimientoRequest->transportado_por_ci = $movimiento->getTransportadoPorCI();

        $movimientoRequest->recibido_por_nombre = $movimiento->getRecibidoPorNombre();
        $movimientoRequest->recibido_por_cargo = $movimiento->getRecibidoPorCargo();
        $movimientoRequest->recibido_por_ci = $movimiento->getRecibidoPorCI();

        foreach($almacenProductoMovimientos as $almacenProductoMovimiento) {
            $movimientoRequest->addAlmacenProductoMovimiento($almacenProductoMovimiento);
        }

        $form = $this->createForm(MovimientoVentaType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cliente = $movimientoRequest->cliente;
            $almacenProductoMovimientos = $movimientoRequest->almacen_producto_movimientos;
            $descripcion = $movimientoRequest->descripcion;

            //
            $movimiento->setEntregadoPorNombre($movimientoRequest->entregado_por_nombre);
            $movimiento->setEntregadoPorCargo($movimientoRequest->entregado_por_cargo);
            $movimiento->setEntregadoPorCI($movimientoRequest->entregado_por_ci);

            $movimiento->setTransportadoPorNombre($movimientoRequest->transportado_por_nombre);
            $movimiento->setTransportadoPorCargo($movimientoRequest->transportado_por_cargo);
            $movimiento->setTransportadoPorCI($movimientoRequest->transportado_por_ci);

            $movimiento->setRecibidoPorNombre($movimientoRequest->recibido_por_nombre);
            $movimiento->setRecibidoPorCargo($movimientoRequest->recibido_por_cargo);
            $movimiento->setRecibidoPorCI($movimientoRequest->recibido_por_ci);

            //

            $movimiento->setEmpresa($cliente);
            if($cliente && ($descripcion === '' || !$descripcion)){
                $descripcion = sprintf('Venta de productos al cliente "%s"', $cliente->getNombre());
            } else {
                $descripcion = 'Venta de productos al cliente';
            }

            $movimiento->setDescripcion($descripcion);

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);


            foreach ($almacenProductoMovimientos as $almacenProductoMovimiento){
                $almacenProductoMovimiento->setMovimiento($movimiento);
                $entityManager->getRepository(ProductoMovimiento::class)->add($almacenProductoMovimiento, $this->getUser(), true);
            }

            $this->notificacionSatisfactoria('Venta de productos modificada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoVenta);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/venta.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/venta/rapida/edit", name="app_mod_ventas_venta_rapida_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarVentaRapida(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {

        $oldMovimiento = clone $movimiento;
        $oldMovimientoVentaRapida = clone $movimiento->getMovimientoVenta();

        $almacenProductoMovimientos = $movimiento->getAlmacenProductoMovimientos();
        $movimientoRequest = new MovimientoVentaRapidaRequest();
        $movimientoRequest->codigo = $movimiento->getCodigo();
        $movimientoRequest->cliente = $movimiento->getEmpresa();

        foreach($almacenProductoMovimientos as $almacenProductoMovimiento) {
            $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
            $almacenProducto->setSaldoDisponible($almacenProducto->getSaldoContable());
            $movimientoRequest->addAlmacenProductoMovimiento($almacenProductoMovimiento);
        }

        $form = $this->createForm(MovimientoVentaRapidaType::class, $movimientoRequest, [
            'movimiento' => $movimiento
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cliente = $movimientoRequest->cliente ?: null;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;


            foreach($movimiento->getAlmacenProductoMovimientos()->toArray() as $almacenProductoMovimiento){
                $movimiento->removeAlmacenProductoMovimiento($almacenProductoMovimiento);
            }

            //
            if($cliente){
                $movimiento->setEmpresa($cliente);
                $movimiento->setDescripcion(sprintf('Venta rápida de productos al cliente "%s"', $cliente->getNombre()));
            } else {
                $movimiento->setDescripcion('Venta rápida de productos');
            }

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;

            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $precioCupVigente = $almacen_producto_movimiento->getPrecioCupVigente() ?: $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup();
                $precioMlcVigente = $almacen_producto_movimiento->getPrecioMlcVigente() ?: $almacen_producto_movimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc();
                $importeTotalVigenteCup += $almacen_producto_movimiento->getCantidad() * $precioCupVigente;
                $importeTotalVigenteMlc += $almacen_producto_movimiento->getCantidad() * $precioMlcVigente;

                $almacen_producto_movimiento->setMovimiento($movimiento);
                $movimiento->addAlmacenProductoMovimiento($almacen_producto_movimiento);
                $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacen_producto_movimiento, $this->getUser(), true);
            }

            $movimiento->setImporteTotalVigenteCup($importeTotalVigenteCup);
            $movimiento->setImporteTotalVigenteMlc($importeTotalVigenteMlc);


            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $this->notificacionSatisfactoria('Venta rápida modificada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoVentaRapida);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/venta.html.twig', [
            'form' => $form,
            'rapida' => true
        ]);
    }

    /**
     * @Route("/{id}/devolucion/edit", name="app_mod_ventas_devolucion_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarDevolucion(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {
        $oldMovimiento = clone $movimiento;
        $oldMovimientoDevolucion = clone $movimiento->getMovimientoDevolucion();

        $almacenProductoMovimientos = $movimiento->getAlmacenProductoMovimientos();

        $movimientoRequest = new MovimientoDevolucionRequest();

        $movimientoRequest->proveedor = $movimiento->getEmpresa();
        $movimientoRequest->descripcion = $movimiento->getDescripcion();

        //
        $movimientoRequest->entregado_por_nombre = $movimiento->getEntregadoPorNombre();
        $movimientoRequest->entregado_por_cargo = $movimiento->getEntregadoPorCargo();
        $movimientoRequest->entregado_por_ci = $movimiento->getEntregadoPorCI();

        $movimientoRequest->transportado_por_nombre = $movimiento->getTransportadoPorNombre();
        $movimientoRequest->transportado_por_cargo = $movimiento->getTransportadoPorCargo();
        $movimientoRequest->transportado_por_ci = $movimiento->getTransportadoPorCI();

        $movimientoRequest->recibido_por_nombre = $movimiento->getRecibidoPorNombre();
        $movimientoRequest->recibido_por_cargo = $movimiento->getRecibidoPorCargo();
        $movimientoRequest->recibido_por_ci = $movimiento->getRecibidoPorCI();

        foreach($almacenProductoMovimientos as $almacenProductoMovimiento) {
            $movimientoRequest->addAlmacenProductoMovimiento($almacenProductoMovimiento);
        }

        $form = $this->createForm(MovimientoDevolucionType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $proveedor = $movimientoRequest->proveedor;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;
            $descripcion = $movimientoRequest->descripcion;

            //
            $movimiento->setEntregadoPorNombre($movimientoRequest->entregado_por_nombre);
            $movimiento->setEntregadoPorCargo($movimientoRequest->entregado_por_cargo);
            $movimiento->setEntregadoPorCI($movimientoRequest->entregado_por_ci);

            $movimiento->setTransportadoPorNombre($movimientoRequest->transportado_por_nombre);
            $movimiento->setTransportadoPorCargo($movimientoRequest->transportado_por_cargo);
            $movimiento->setTransportadoPorCI($movimientoRequest->transportado_por_ci);

            $movimiento->setRecibidoPorNombre($movimientoRequest->recibido_por_nombre);
            $movimiento->setRecibidoPorCargo($movimientoRequest->recibido_por_cargo);
            $movimiento->setRecibidoPorCI($movimientoRequest->recibido_por_ci);

            //
            $movimiento->setEmpresa($proveedor);
            $movimiento->setDescripcion($descripcion ?: sprintf('Devolución de productos a proveedor "%s"', $proveedor->getNombre()));

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);


            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $almacen_producto_movimiento->setMovimiento($movimiento);
                $entityManager->getRepository(ProductoMovimiento::class)->add($almacen_producto_movimiento, $this->getUser(), true);
            }

            $this->notificacionSatisfactoria('Devolución a proveedor modificada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoDevolucion);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/devolucion.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/gasto_aporte/edit", name="app_mod_ventas_gasto_aporte_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarGastoAporte(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {
        $oldMovimiento = clone $movimiento;
        $oldMovimientoGastoAporte = clone $movimiento->getMovimientoGastoAporte();

        $almacenProductoMovimientos = $movimiento->getAlmacenProductoMovimientos();
        $movimientoRequest = new MovimientoGastoAporteRequest();

        $movimientoRequest->codigo = $movimiento->getCodigo();
        $movimientoRequest->cliente = $movimiento->getEmpresa();
        $movimientoRequest->descripcion = $movimiento->getDescripcion();

        foreach($almacenProductoMovimientos as $almacenProductoMovimiento) {
            $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
            $almacenProducto->setSaldoDisponible($almacenProducto->getSaldoContable());
            $movimientoRequest->addAlmacenProductoMovimiento($almacenProductoMovimiento);
        }

        $form = $this->createForm(MovimientoGastoAporteType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cliente = $movimientoRequest->cliente ?: null;
            $descripcion = $movimientoRequest->descripcion ?: null;
            $almacen_producto_movimientos = $movimientoRequest->almacen_producto_movimientos;


            foreach($movimiento->getAlmacenProductoMovimientos()->toArray() as $almacenProductoMovimiento){
                $movimiento->removeAlmacenProductoMovimiento($almacenProductoMovimiento);
            }

            //
            $movimiento->setEmpresa($cliente);
            $movimiento->setDescripcion($descripcion ?: sprintf('Gasto de aporte al cliente "%s"', $cliente->getNombre()));

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            foreach ($almacen_producto_movimientos as $almacen_producto_movimiento){
                $almacen_producto_movimiento->setMovimiento($movimiento);
                $movimiento->addAlmacenProductoMovimiento($almacen_producto_movimiento);
                $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacen_producto_movimiento, $this->getUser(), true);
            }

            $this->notificacionSatisfactoria('Venta rápida modificada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoGastoAporte);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/gasto_aporte.html.twig', [
            'form' => $form
        ]);
    }
}
