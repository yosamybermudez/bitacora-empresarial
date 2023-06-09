<?php

namespace ModCompras\Controller;

use App\Controller\AppController;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compras")
 */
class ModComprasController extends AppController
{
    private $tipo_movimiento = 'compras';

    /**
     * @Route("/", name="app_mod_compras_dashboard")
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
                $this->eventoExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
//                $this->eventoError(sprintf('No se pudo acceder al enlace "%s". Causa: Fecha no válida', $this->obtenerEnlaceActual()));
                return $this->redirectToRoute('app_movimiento_tipo_index', ['tipo' => $this->tipo_movimiento]);
            }
        }

        $movimientos = $movimientoRepository->findMovimientosFecha($this->tipo_movimiento, $fecha);

        return $this->render('mod_compras/dashboard.html.twig', [
            'movimientos' => $movimientos,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tipo' => $this->tipo_movimiento
        ]);
    }

    /**
     * @Route("/listar", name="app_mod_compras_list")
     */
    public function list(Request $request, MovimientoRepository $movimientoRepository): Response
    {
        $fecha = null;

        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->eventoExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
//                $this->eventoError(sprintf('No se pudo acceder al enlace "%s". Causa: Fecha no válida', $this->obtenerEnlaceActual()));

                return $this->redirectToRoute('app_mod_compras_dashboard');
            }
        }

        $movimientos = $movimientoRepository->findMovimientosFecha($this->tipo_movimiento, $fecha);

        return $this->render('mod_compras/dashboard.html.twig', [
            'movimientos' => $movimientos,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tipo' => $this->tipo_movimiento
        ]);
    }

    /**
     * @Route("/entrada/registrar", name="app_movimiento_entrada_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarEntrada(Request $request, EntityManagerInterface $entityManager): Response
    {

        $almacenes = $entityManager->getRepository(Almacen::class)->findAll();

        if(count($almacenes) === 0){
            $this->notificacionError('No puede realizar una entrada cuando no existen almacenes registrados. Por favor, registre al menos un almacén');
//            $this->eventoError('Registro de entrada cancelado. Causa: No hay almacenes disponibles para el almacenamientos de los productos');
            return $this->redirectToRoute('app_almacen_new', [], Response::HTTP_SEE_OTHER);
        }

        $movimientoRequest = new MovimientoEntradaRequest();
        $movimientoRequest->addProductoMovimiento(new ProductoMovimiento());
        $movimientoRequest->descripcion = 'Entrada de productos desde el proveedor';
        $movimientoRequest->fecha = date('d-m-Y');


        $form = $this->createForm(MovimientoEntradaType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $almacen_destino = $movimientoRequest->almacen_destino;
            $fecha = $movimientoRequest->fecha;
            $proveedor = $movimientoRequest->proveedor;
            $producto_movimientos = $movimientoRequest->producto_movimientos;

            //Generar el codigo del movimiento. Ejemplo: ENT202301010001
            $codigo = 'ENT' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoEntrada::class)->findAll()) + 1) ;

            //
            $movimiento = new Movimiento();

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
            if($proveedor){
                $movimiento->setEmpresa($proveedor);
                $descripcion = sprintf('Entrada desde el proveedor "%s" para "%s"', $proveedor->getNombre(), $almacen_destino->getNombre());
            } else {
                $descripcion = sprintf('Entrada para "%s"', $almacen_destino->getNombre());
            }
            $movimiento->setDescripcion($descripcion);
            $movimiento->setFecha(new \DateTime($fecha));
            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Entrada');

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(),true);

            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($producto_movimientos as $producto_movimiento){
                $importeTotalVigenteCup += $producto_movimiento->getCantidad() * $producto_movimiento->getProducto()->getPrecioCompraCup();
                $importeTotalVigenteMlc += $producto_movimiento->getCantidad() * $producto_movimiento->getProducto()->getPrecioCompraMlc();

                //
                $movimiento->addProductoMovimiento($producto_movimiento);
                $producto_movimiento->setMovimiento($movimiento);
                $entityManager->getRepository(ProductoMovimiento::class)->add($producto_movimiento, $this->getUser(), true);
            }

            $movimiento->setImporteTotalVigenteCup($importeTotalVigenteCup);
            $movimiento->setImporteTotalVigenteMlc($importeTotalVigenteMlc);

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Registrar el movimiento creado, como una Entrada a almacen
            $movimientoEntrada = new MovimientoEntrada();
            $movimientoEntrada->setAlmacen($almacen_destino);
            $movimientoEntrada->setMovimiento($movimiento);

            $entityManager->getRepository(MovimientoEntrada::class)->add($movimientoEntrada, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                dd($movimiento);
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Entrada de productos ' . $movimiento->getCodigo() . '  registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_entrada_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Entrada de productos registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_entrada_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Entrada de productos ' . $movimiento->getCodigo() . '  registrada y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);

                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Entrada de productos registrada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('movimiento/entrada.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/retorno/registrar", name="app_movimiento_retorno_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function registrarRetorno(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Solo se puede realizar un retorno si existen clientes
        $clientes = $entityManager->getRepository(Organizacion::class)->findBy(['esCliente' => true]);
        if(!$clientes){
            $this->notificacionError('No puede realizar un retorno cuando no existen clientes registrados');
//            $this->eventoError('Registro de retorno cancelado. Causa: No hay clientes registrados');
            return $this->redirectToRoute('app_movimiento_index', [], Response::HTTP_SEE_OTHER);
        }
        $movimientoRequest = new MovimientoRetornoRequest();
        $movimientoRequest->addProductoMovimiento(new ProductoMovimiento());
        $movimientoRequest->descripcion = 'Retorno de productos desde el cliente';
        $movimientoRequest->fecha = date('d-m-Y');

        $form = $this->createForm(MovimientoRetornoType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $almacen_destino = $movimientoRequest->almacen_destino;
            $cliente = $movimientoRequest->cliente;
            $producto_movimientos = $movimientoRequest->producto_movimientos;
            $fecha = $movimientoRequest->fecha;


            //
            $codigo = 'RET' . date('Ymd') . $this->numeroConCeros(count($entityManager->getRepository(MovimientoRetorno::class)->findAll()) + 1) ;

            //
            $movimiento = new Movimiento();

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

            $movimiento->setFecha(new \DateTime($fecha));
            $movimiento->setEstado('Sin confirmar');


            $movimiento->setEmpresa($cliente);
            $movimiento->setDescripcion(sprintf('Retorno de productos desde cliente "%s" para "%s"', $cliente->getNombre(), $almacen_destino->getNombre()));
            $movimiento->setCodigo($codigo);
            $movimiento->setTipo('Retorno');

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $movimientoEstado = new MovimientoEstado($movimiento, 'Sin confirmar');
            $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(),true);

            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
            foreach ($producto_movimientos as $producto_movimiento){
                $importeTotalVigenteCup += $producto_movimiento->getCantidad() * $producto_movimiento->getProducto()->getPrecioVentaCup();
                $importeTotalVigenteMlc += $producto_movimiento->getCantidad() * $producto_movimiento->getProducto()->getPrecioVentaMlc();

                //
                $movimiento->addProductoMovimiento($producto_movimiento);

                $producto_movimiento->setMovimiento($movimiento);

                $entityManager->getRepository(ProductoMovimiento::class)->add($producto_movimiento, $this->getUser(), true);
            }
            $movimiento->setImporteTotalVigenteCup($importeTotalVigenteCup);
            $movimiento->setImporteTotalVigenteMlc($importeTotalVigenteMlc);

            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            // Registrar el movimiento creado, como un Retorno del cliente, hacia almacen
            $movimientoRetorno = new MovimientoRetorno();
            $movimientoRetorno->setAlmacen($almacen_destino);
            $movimientoRetorno->setMovimiento($movimiento);

            $entityManager->getRepository(MovimientoRetorno::class)->add($movimientoRetorno, true);

            //
            $params = $request->request->all();

            if(array_key_exists('save_confirm_add_new', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Retorno de productos (desde cliente) ' . $movimiento->getCodigo() . ' registrado y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_retorno_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_add_new', $params)){
                $this->notificacionSatisfactoria('Retorno de productos (desde cliente) registrado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_retorno_new', [], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save_confirm', $params)){
                $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);
                $this->notificacionSatisfactoria('Retorno de productos (desde cliente) ' . $movimiento->getCodigo() . '  registrado y confirmada satisfactoriamente');
                $this->eventoRegistroSatisfactorio();
                $this->eventoMovimientoConfirmar($movimiento);
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
            if(array_key_exists('save', $params)){
                $this->notificacionSatisfactoria('Retorno de productos (desde cliente) registrado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento');
                $this->eventoRegistroSatisfactorio();
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->renderForm('movimiento/retorno.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/entrada/edit", name="app_movimiento_entrada_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarEntrada(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {
        $oldMovimiento = clone $movimiento;
        $oldMovimientoEntrada = clone $movimiento->getMovimientoEntrada();

        $productosMovimientos = $movimiento->getProductoMovimientos();

        $movimientoRequest = new MovimientoEntradaRequest();

        $movimientoRequest->almacen_destino = $movimiento->getMovimientoEntrada()->getAlmacen();
        $movimientoRequest->proveedor = $movimiento->getEmpresa();
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

        foreach($productosMovimientos as $productoMovimiento) {
            $movimientoRequest->addProductoMovimiento($productoMovimiento);
        }

        $form = $this->createForm(MovimientoEntradaType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $requestAlmacenDestino = $movimientoRequest->almacen_destino;
            $requestProveedor = $movimientoRequest->proveedor;
            $requestProductoMovimientos = $movimientoRequest->producto_movimientos;
            $requestDescripcion = $movimientoRequest->descripcion;

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
            $movimiento->setEmpresa($requestProveedor);
            $movimiento->setDescripcion($requestDescripcion?: sprintf('Entrada de productos desde proveedor "%s" para "%s"', $requestProveedor->getNombre() ?: "N/E", $requestAlmacenDestino->getNombre()));
            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $movimientoEntrada = $movimiento->getMovimientoEntrada();
            $movimientoEntrada->setAlmacen($requestAlmacenDestino);
            $entityManager->getRepository(MovimientoEntrada::class)->add($movimientoEntrada, true);

            foreach ($requestProductoMovimientos as $productoMovimiento){
                $productoMovimiento->setMovimiento($movimiento);
                $entityManager->getRepository(ProductoMovimiento::class)->add($productoMovimiento, $this->getUser(), true);
            }

            $this->notificacionSatisfactoria('Entrada de productos modificada satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoEntrada);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/entrada.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/retorno/edit", name="app_movimiento_retono_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function editarRetorno(Request $request, EntityManagerInterface $entityManager, Movimiento $movimiento): Response
    {
        $oldMovimiento = clone $movimiento;
        $oldMovimientoRetorno = clone $movimiento->getMovimientoRetorno();

        $productosMovimientos = $movimiento->getProductoMovimientos();

        $movimientoRequest = new MovimientoRetornoRequest();

        $movimientoRequest->almacen_destino = $movimiento->getMovimientoRetorno()->getAlmacen();
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

        foreach($productosMovimientos as $productoMovimiento) {
            $movimientoRequest->addProductoMovimiento($productoMovimiento);
        }

        $form = $this->createForm(MovimientoRetornoType::class, $movimientoRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $requestAlmacenDestino = $movimientoRequest->almacen_destino;
            $requestCliente = $movimientoRequest->cliente;
            $requestProductoMovimientos = $movimientoRequest->producto_movimientos;
            $requestDescripcion = $movimientoRequest->descripcion;
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
            $movimiento->setEmpresa($requestCliente);
            $movimiento->setDescripcion($requestDescripcion ?: sprintf('Retorno de productos desde cliente "%s" para "%s"', $requestCliente->getNombre(), $requestAlmacenDestino->getNombre()));
            $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(),true);

            $movimientoRetorno = $movimiento->getMovimientoRetorno();
            $movimientoRetorno->setAlmacen($requestAlmacenDestino);
            $entityManager->getRepository(MovimientoRetorno::class)->add($movimientoRetorno, true);

            foreach ($requestProductoMovimientos as $productoMovimiento){
                $productoMovimiento->setMovimiento($movimiento);
                $entityManager->getRepository(ProductoMovimiento::class)->add($productoMovimiento, $this->getUser(), true);
            }
            $this->notificacionSatisfactoria('Retorno a cliente modificado satisfactoriamente. Para que se actualicen los saldos debe confirmar el movimiento.');
            $this->eventoModificacionSatisfactoria($oldMovimiento);
            $this->eventoModificacionSatisfactoria($oldMovimientoRetorno);
            return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movimiento/retorno.html.twig', [
            'form' => $form,
        ]);
    }
}
