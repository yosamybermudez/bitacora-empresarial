<?php

namespace ModInventario\Controller;

use App\Controller\AppController;
use ModInventario\Entity\AlmacenProducto;
use ModInventario\Entity\AlmacenProductoMovimiento;
use ModInventario\Entity\Movimiento;
use ModInventario\Entity\MovimientoEstado;
use ModInventario\Entity\ProductoMovimiento;
use ModInventario\Repository\MovimientoRepository;
use ModInventario\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;
use NcJoes\OfficeConverter\OfficeConverter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/inventario/movimiento")
 */
class MovimientoController extends AppController
{
    /**
     * @Route("/", name="app_movimiento_index", methods={"GET"})
     */
    public function index(Request $request, MovimientoRepository $movimientoRepository, EntityManagerInterface $entityManager): Response
    {
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                return $this->redirectToRoute('app_movimiento_index');
            }
        }
        $movimientos = $movimientoRepository->findMovimientosFecha('', $fecha);
        return $this->render('movimiento/index.html.twig', [
            'movimientos' => $movimientos,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : ''
        ]);
    }

    /**
     * @Route("/unconfirmed", name="app_movimiento_unconfirmed", methods={"GET"})
     */
    public function unconfirmed(Request $request, MovimientoRepository $movimientoRepository, EntityManagerInterface $entityManager): Response
    {

        $movimientos = $movimientoRepository->findBy(['estado' => 'Sin confirmar']);
        return $this->render('movimiento/index.html.twig', [
            'movimientos' => $movimientos
        ]);
    }

    /**
     * @Route("/tipo/{tipo}", name="app_movimiento_tipo_index", methods={"GET"})
     */
    public function indexTipo(Request $request, MovimientoRepository $movimientoRepository, string $tipo): Response
    {
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                return $this->redirectToRoute('app_movimiento_tipo_index', ['tipo' => $tipo]);
            }
        }
        if(!in_array($tipo, ['entradas', 'ventas', 'devoluciones', 'retornos', 'gastosDeAporte', 'ajustes', 'transferencias']))
        {
            $this->notificacionError('Enlace no válido. Se redirecciona hacia el listado general de movimientos');
            return $this->redirectToRoute('app_movimiento_index');
        }
        $movimientos = $movimientoRepository->findMovimientosFecha($tipo, $fecha);

        return $this->render('movimiento/index.html.twig', [
            'movimientos' => $movimientos,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : '',
            'tipo' => $tipo
        ]);
    }


    /**
     * @Route("/resumen/exportar/{frecuencia}/{tipo}", name="app_movimiento_resumen_exportar", priority="25", methods={"GET", "POST"})
     */
    public function exportarResumen(Request $request, MovimientoRepository $movimientoRepository, ProductoRepository $productoRepository, string $frecuencia, string $tipo = ''): Response
    {
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            $fecha = strlen($fecha) === 8 ? $fecha : $fecha . '01';
            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                return  $this->redirectToRoute('app_movimiento_resumen', ['frecuencia' => $frecuencia, 'fecha' => $frecuencia === 'dia' ? date('Ymd', $fecha) : date('Ym', $fecha)]);
            }
        }

        $tabla = $this->tablaResumen($movimientoRepository,$productoRepository,$tipo,$frecuencia,$fecha);

        $spreadsheet = new Spreadsheet();
        $allOpciones = ['ventas', 'entradas', 'retornos', 'devoluciones', 'gastos de aporte'];
        if(!$tipo){
            $opciones = $allOpciones;
            $spreadsheet->removeSheetByIndex(0);
            foreach ($opciones as $opcion){
                $spreadsheet->addSheet(new Worksheet(null, ucfirst($opcion)));
            }
        } else {
            $opciones = [$tipo];
            $spreadsheet->getActiveSheet()->setTitle(ucfirst($tipo));
        }


        foreach ($opciones as $opcion) {
/////////////////////////////////////////
///
            $x= in_array($opcion, ['entradas', 'devoluciones', 'gastos de aporte']) ? 'compra' : 'venta';
            $columnas = ['Saldo inicial', 'U/M', 'Producto'];
            $columnas[] = ['Precios ' . $x => ['CUP', 'MLC']];

            $columnas[] = [ucfirst($opcion) => []];
            //Dias / Meses
            $cantidad = 0;
            switch ($frecuencia){
                case 'mes': {
                    $final = clone $fecha;
                    $final->modify('last day of this month');
                    for($i = 1; $i <= intval($final->format('d')); $i++){
                        $columnas[4][ucfirst($opcion)][] = $i;
                    }
                    $cantidad = intval($final->format('d'));
                    break;
                }
                case 'anno' : {
                    for($i = 1; $i <= 12; $i++){
                        $columnas[4][ucfirst($opcion)][] = $this->filter->resumenMesFilter($i);
                    }
                    $cantidad = 12;
                    break;
                }
            }

            //Cabeceras de Totales
            $temp = $allOpciones;
            $key = array_search($opcion, $allOpciones);
            unset($temp[$key]);
            $totalesOrden = ['Total ' . ucfirst($opcion)];
            foreach ($temp as $t){
                $totalesOrden[] = 'Total ' . ucfirst($t);
            }
            $columnasFinales = (array_merge($columnas,$totalesOrden,['Saldo contable']));

            $spreadsheet->setActiveSheetIndexByName(ucfirst($opcion));
            $sheet = $spreadsheet->getActiveSheet();

            $colIndex = 1;
            foreach ($columnasFinales as $columna){
                if(is_array($columna)){
                    foreach ($columna as $key => $value){
                        $mergeCant = count($value);
                        $sheet->setCellValue([$colIndex, 1], $key);
                        $k = 0;
                        for($i = $colIndex; $i <= $colIndex + $mergeCant; $i++){
                            $sheet->setCellValue([$i, 2], isset($value[$k]) ? $value[$k] : '');
                            $k++;
                        }
                        $sheet->mergeCells([$colIndex, 1, $colIndex + $mergeCant - 1, 1]);
                        $colIndex+= $mergeCant;
                    }
                } else {
                    $sheet->setCellValue([$colIndex, 1], $columna);
                    $sheet->mergeCells([$colIndex, 1, $colIndex, 2]);
                    $colIndex++;

                }
            }
            // Los resultados a partir de la fila 3
            $row = 3;
            foreach ($tabla as $nombre => $valor){
                $column = 1;
                $sheet->setCellValue([$column,$row], $valor['saldo_inicial']); $column++; //Saldo inicial
                $sheet->setCellValue([$column,$row], $valor['unidad_medida']); $column++; // U/M
                $sheet->setCellValue([$column,$row], $nombre); $column++; //Producto
                $sheet->setCellValue([$column,$row], $valor['precios'][$x]['cup']); $column++; //PRecio CUP
                $sheet->setCellValue([$column,$row], $valor['precios'][$x]['mlc']); $column++; //PRecio MLC

                //Llenando columnas de dias o meses
                $arr = isset($valor[$opcion][$frecuencia]) ? $valor[$opcion][$frecuencia] : [];
                foreach($arr as $fecha => $total){
                    switch (strlen($fecha)){
                        case 6: { $fecha = $fecha . '01'; break; }
                        case 4: { $fecha = $fecha . '0101'; break; }
                    }
                    $fecha = new \DateTime($fecha);
                    $numero = $frecuencia === 'mes' ? $fecha->format('j') : $fecha->format('m');
                    $sheet->setCellValue([$column + $numero - 1, $row], $total);
                }
                $column+= $cantidad;

                //Llenando columnas de totales
                foreach ($totalesOrden as $total){
                    $o = strtolower(substr($total, 6));
                    if(!isset($valor['total'][$o]))
                        $valor['total'][$o] = 0;
                    $sheet->setCellValue([$column,$row], $valor['total'][$o]);
                    $column++;
                }
                $sheet->setCellValue([$column,$row], $valor['saldo_contable']); $column++;
                $row++;
            }

/////////////////////////////////////////
        }

        $spreadsheet->setActiveSheetIndex(0);

        for($i = 'A'; $i != $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Resumen.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }



    /**
     * @Route("/resumen/{frecuencia}/{tipo}", name="app_movimiento_resumen", priority="25", methods={"GET", "POST"})
     */
    public function resumen(Request $request, MovimientoRepository $movimientoRepository, ProductoRepository $productoRepository, string $frecuencia, string $tipo = ''): Response
    {
        if(!in_array($frecuencia, ['dia', 'mes', 'anno'])){
            return  $this->redirectToRoute('app_movimiento_resumen', ['frecuencia' => 'dia', 'fecha' => date('Ymd')]);
        }
        $fecha = null;
        if(array_key_exists('fecha', $request->query->all())){
            $fecha = $request->query->get('fecha');
            switch (strlen($fecha)){
                case 6: { $fecha = $fecha . '01'; break; }
                case 4: { $fecha = $fecha . '0101'; break; }
            }

            try{
                $fecha = new \DateTime($fecha);
            } catch (\Exception $e){
                $this->notificacionExcepcion($e->getMessage());
                $this->notificacionError('Fecha no válida');
                $f = $this->fechaFormat($frecuencia);
                return  $this->redirectToRoute('app_movimiento_resumen', ['frecuencia' => $frecuencia, 'fecha' => $f]);
            }
        } else {
            $f = $this->fechaFormat($frecuencia);
            return  $this->redirectToRoute('app_movimiento_resumen', ['frecuencia' => $frecuencia, 'fecha' => $f]);
        }
        $tabla = $this->tablaResumen($movimientoRepository, $productoRepository, $tipo, $frecuencia, $fecha);


        $form = $this->createFormBuilder()
            ->add('fecha', TextType::class, [
                'label' => 'Fecha',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'calendarpicker',
                    'data-max-date' => date('d-m-Y'),
                    'data-locale' => 'es-ES',
                    'data-input-format' => '%d-%m-%Y',
                    'data-format' => '%d-%m-%Y',
                    'data-on-change' => 'submit_form()'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $fecha = $form->getData()['fecha'] ?: null;
            $fecha = new \DateTime($fecha);
            return $this->redirectToRoute('app_movimiento_resumen', ['frecuencia' => $frecuencia, 'fecha' => $frecuencia === 'dia' ? $fecha->format('Ymd') : $fecha->format('Ym')], Response::HTTP_SEE_OTHER);
        }


        return $this->render('movimiento/resumen.html.twig', [
            'form' => $form->createView(),
            'tipo' => $tipo,
            'frecuencia' => $frecuencia,
            'tabla' => $tabla,
            'fecha' => $fecha ? $fecha->format('d-m-Y') : ''
        ]);
    }

    private function fechaFormat(string $frecuencia, \DateTime $fecha = null){
        if(!$fecha){
            $fecha = new \DateTime();
        }
        switch ($frecuencia){
            case 'dia': {
                return $fecha->format('Ymd');
                break;
            }
            case 'mes': {
               return $fecha->format('Ym');
            }
            case 'anno' : {
                return $fecha->format('Y');
            }
        }
    }

    private function tablaResumen(MovimientoRepository $movimientoRepository, ProductoRepository $productoRepository, string $tipo, string $frecuencia = '', \DateTime $fecha = null){
        if($tipo === ''){
            $opciones = ['ventas', 'entradas', 'retornos', 'devoluciones', 'gastos de aporte', 'transferencias', 'ajustes'];
        } else {
            $opciones = [$tipo];
        }
        $tabla = [];
        foreach ($opciones as $opcion){
            $mov = $movimientoRepository->findResumenFecha($fecha, $frecuencia, $opcion);
            foreach ($mov as $m){
                $producto = isset($m['id']) ? $productoRepository->findSaldoContableMovimiento($m['id']) : [];

                $saldoContable = count($producto) > 0 ? $producto['saldoContable'] : 0;
                $tabla[$m['nombre']]['saldo_contable'] = $saldoContable;

                //
             //   if(intval($m['cantidad']) !== 0){
                    if(!isset($tabla[$m['nombre']][$opcion]['mes'][$m['fecha']->format('Ymd')]))
                        $tabla[$m['nombre']][$opcion]['mes'][$m['fecha']->format('Ymd')] = 0;
                    $tabla[$m['nombre']][$opcion]['mes'][$m['fecha']->format('Ymd')] += $m['cantidad'];

                    if(!isset($tabla[$m['nombre']][$opcion]['anno'][$m['fecha']->format('Ym')]))
                        $tabla[$m['nombre']][$opcion]['anno'][$m['fecha']->format('Ym')] = 0;
                    $tabla[$m['nombre']][$opcion]['anno'][$m['fecha']->format('Ym')] += $m['cantidad'];
           //     }


                $tabla[$m['nombre']]['id'] = $m['id'];
                $tabla[$m['nombre']]['unidad_medida'] = $m['unidadMedida'];
                $tabla[$m['nombre']]['precios']['compra']['cup'] = $this->_number_format($m['precioCompraCup']);
                $tabla[$m['nombre']]['precios']['compra']['mlc'] = $this->_number_format($m['precioCompraMlc']);
                $tabla[$m['nombre']]['precios']['venta']['cup'] = $this->_number_format($m['precioVentaCup']);
                $tabla[$m['nombre']]['precios']['venta']['mlc'] = $this->_number_format($m['precioVentaMlc']);
                $tabla[$m['nombre']]['saldo_inicial'] = $saldoContable;
//                $tabla[$m['nombre']]['fecha'] = $m['fecha']->format('Ymd');

                $tabla[$m['nombre']]['total'][$opcion]
                    = isset($tabla[$m['nombre']][$opcion]['mes']) ? array_sum($tabla[$m['nombre']][$opcion]['mes']) : '';
                $tabla[$m['nombre']]['saldo_inicial']
                    += (isset($tabla[$m['nombre']]['total']['ventas']) and $tabla[$m['nombre']]['total']['ventas'] != '') ? $tabla[$m['nombre']]['total']['ventas'] : 0;
                $tabla[$m['nombre']]['saldo_inicial']
                    += (isset($tabla[$m['nombre']]['total']['devoluciones']) and $tabla[$m['nombre']]['total']['devoluciones'] != '') ? $tabla[$m['nombre']]['total']['devoluciones'] : 0;
                $tabla[$m['nombre']]['saldo_inicial']
                    -= (isset($tabla[$m['nombre']]['total']['entradas']) and $tabla[$m['nombre']]['total']['entradas'] != '') ? $tabla[$m['nombre']]['total']['entradas'] : 0;
                $tabla[$m['nombre']]['saldo_inicial']
                    -= (isset($tabla[$m['nombre']]['total']['retornos']) and $tabla[$m['nombre']]['total']['retornos'] != '') ? $tabla[$m['nombre']]['total']['retornos'] : 0;
            }
        }
        return $tabla;
    }

//    private function tablaResumenDiario(string $tipo, $fecha, string $frecuencia, MovimientoRepository $movimientoRepository, ProductoRepository $productoRepository){
//        if($tipo === ''){
//            $opciones = ['ventas', 'entradas', 'retornos', 'devoluciones'];
//        } else {
//            $opciones = [$tipo];
//        }
//        foreach ($opciones as $opcion){
//            $mov = $movimientoRepository->findResumenFecha($fecha, $frecuencia, $opcion);
//            foreach ($mov as $m){
//                $producto = isset($m['id']) ? $productoRepository->findSaldoContableMovimiento($m['id']) : [];
//
//                $saldoContable = count($producto) > 0 ? $producto['saldoContable'] : 0;
//                $tabla[$m['nombre']]['saldo_contable'] = $saldoContable;
//
//                if(!isset($tabla[$m['nombre']][$opcion]['cantidad']))
//                    $tabla[$m['nombre']][$opcion]['cantidad'] = 0;
//                $tabla[$m['nombre']][$opcion]['cantidad'] += number_format(floatval($m['cantidad']),2,'.','');
//                $tabla[$m['nombre']]['id'] = $m['id'];
//                $tabla[$m['nombre']]['unidad_medida'] = $m['unidadMedida'];
//                $tabla[$m['nombre']]['precios']['compra'] = sprintf("%s CUP, %s MLC", $this->_number_format($m['precioCompraCup']), $this->_number_format($m['precioCompraMlc']));
//                $tabla[$m['nombre']]['precios']['venta'] = sprintf("%s CUP, %s MLC", $this->_number_format($m['precioVentaCup']), $this->_number_format($m['precioVentaMlc']));
//                $tabla[$m['nombre']]['saldo_inicial'] = $saldoContable;
//
//                //
//                $tabla[$m['nombre']]['saldo_inicial'] += isset($tabla[$m['nombre']]['ventas']['cantidad']) ? $tabla[$m['nombre']]['ventas']['cantidad'] : 0;
//                $tabla[$m['nombre']]['saldo_inicial'] += isset($tabla[$m['nombre']]['devoluciones']['cantidad']) ? $tabla[$m['nombre']]['devoluciones']['cantidad'] : 0;
//                $tabla[$m['nombre']]['saldo_inicial'] -= isset($tabla[$m['nombre']]['entradas']['cantidad']) ? $tabla[$m['nombre']]['entradas']['cantidad'] : 0;
//                $tabla[$m['nombre']]['saldo_inicial'] -= isset($tabla[$m['nombre']]['retornos']['cantidad']) ? $tabla[$m['nombre']]['retornos']['cantidad'] : 0;
//
//            }
//        }
//        return $tabla;
//    }

    /**
     * @Route("/{id}/edit", name="app_movimiento_edit", methods={"GET", "POST"})
     */
    public function edit(Movimiento $movimiento, EntityManagerInterface $entityManager): Response
    {
        $tipoMovimiento = substr($movimiento->getCodigo(),0,3);
        switch ($tipoMovimiento){
            case 'ENT': {
                return $this->redirectToRoute('app_movimiento_entrada_edit', ['id' => $movimiento->getId()]);
            }
            case 'RET': {
                return $this->redirectToRoute('app_movimiento_retorno_edit', ['id' => $movimiento->getId()]);
            }
            case 'VEN': {
                if($movimiento->isLite())
                    return $this->redirectToRoute('app_movimiento_venta_rapida_edit', ['id' => $movimiento->getId()]);
                return $this->redirectToRoute('app_movimiento_venta_edit', ['id' => $movimiento->getId()]);
            }
            case 'DEV': {
                return $this->redirectToRoute('app_movimiento_devolucion_edit', ['id' => $movimiento->getId()]);
            }
            case 'GAP': {
                return $this->redirectToRoute('app_movimiento_gasto_aporte_edit', ['id' => $movimiento->getId()]);
            }
            case 'TRF': {
                return $this->redirectToRoute('app_movimiento_transferencia_almacen_edit', ['id' => $movimiento->getId()]);
            }
            case 'AJT': {
                return $this->redirectToRoute('app_movimiento_ajuste_inventario_edit', ['id' => $movimiento->getId()]);
            }
        }
        $this->notificacionError('Ha ocurrido un error inesperado al acceder al enlace. Se redireccionará a la página principal');
        return $this->redirectToRoute('app_main');
    }







    /**
     * @Route("/{id}/confirm", name="app_movimiento_confirm", methods={"GET", "POST"})
     */
    public function confirmar(Movimiento $movimiento, EntityManagerInterface $entityManager, MovimientoRepository $movimientoRepository): Response
    {
        $codigo = $this->filter->tipoMovimientoFilter($movimiento->getCodigo());

        if(!$this->isGranted('ROLE_GESTOR')){
            if(
                ($this->isGranted("ROLE_EDITOR") and !in_array($codigo, ['VEN', 'GAP', 'RET'])) ||
                ($this->isGranted("ROLE_EDITOR") and !in_array($codigo, ['ENT', 'DEV'])) ||
                (($this->isGranted("ROLE_EDITOR") and !in_array($codigo, ['TRF', 'AJT'])))
            )
                throw new AccessDeniedException('El usuario no tiene acceso al recurso solicitado');
        }


        $movimiento = $this->confirmarMovimiento($movimiento, $entityManager);

        $this->notificacionSatisfactoria('Movimiento: ' . $movimiento->getCodigo() . ' confirmado satisfactoriamente');
        return $this->redirectToReferer();
    }

    public function confirmarMovimiento(Movimiento $movimiento, EntityManagerInterface $entityManager)
    {
        // Se actualizan los saldos de los productos entrados en el almacen destino, depende del tipo de movimiento
        $tipoMovimiento = substr($movimiento->getCodigo(),0,3);
        switch ($tipoMovimiento){
            case 'ENT':
            case 'RET':
                {
                    $movimientoTemp = ($tipoMovimiento === 'ENT')
                        ? $movimiento->getMovimientoEntrada()
                        : $movimiento->getMovimientoRetorno();

                    foreach ($movimiento->getProductoMovimientos() as $productoMovimiento){
                        $almacen = $movimientoTemp->getAlmacen();
                        $producto = $productoMovimiento->getProducto();
                        $almacenProducto =
                            $entityManager->getRepository(AlmacenProducto::class)->findOneBy(['almacen' => $almacen, 'producto' => $producto]);

                        if(!$almacenProducto){
                            $almacenProducto = new AlmacenProducto();
                            $almacenProducto->setProducto($producto);
                            $almacenProducto->setAlmacen($almacen);
                        }

                        $almacenProducto->setSaldoContable($almacenProducto->getSaldoContable() + $productoMovimiento->getCantidad());
                        $almacenProducto->setSaldoDisponible($almacenProducto->getSaldoDisponible() + $productoMovimiento->getCantidad());
                        $entityManager->getRepository(AlmacenProducto::class)->add($almacenProducto, $this->getUser(), true);

                        $producto = $almacenProducto->getProducto();
                        $productoMovimiento->setPrecioCupVigente(in_array($tipoMovimiento, ['RET']) ? $producto->getPrecioVentaCup() : $producto->getPrecioCompraCup());
                        $productoMovimiento->setPrecioMlcVigente(in_array($tipoMovimiento, ['RET']) ? $producto->getPrecioVentaMlc() : $producto->getPrecioCompraMlc());
                        $entityManager->getRepository(ProductoMovimiento::class)->add($productoMovimiento, $this->getUser(), true);

                    }
                    break;
                }
            case 'VEN':
            case 'DEV':
            case 'GAP':
                {
                    // Se descuenta del almacen, la cantidad de cada producto
                    foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                        $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
                        $nuevoSaldoContable = $almacenProducto->getSaldoContable() - $almacenProductoMovimiento->getCantidad();
                        $almacenProducto->setSaldoContable($nuevoSaldoContable);
                        $producto = $almacenProducto->getProducto();
                        $almacenProductoMovimiento->setPrecioCupVigente(in_array($tipoMovimiento, ['VEN']) ? $producto->getPrecioVentaCup() : $producto->getPrecioCompraCup());
                        $almacenProductoMovimiento->setPrecioMlcVigente(in_array($tipoMovimiento, ['VEN']) ? $producto->getPrecioVentaMlc() : $producto->getPrecioCompraMlc());

                        $entityManager->getRepository(AlmacenProducto::class)->add($almacenProducto, $this->getUser(), true);
                        $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacenProductoMovimiento, $this->getUser(), true);
                    }

                    break;
                }
            case 'TRF': {
                $almacenDestino = $movimiento->getMovimientoTransferenciaAlmacen()->getAlmacen();
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                    $producto = $almacenProductoMovimiento->getAlmacenProducto()->getProducto();
                    $almacenProductoNuevo = $entityManager->getRepository(AlmacenProducto::class)->findOneBy(['almacen' => $almacenDestino, 'producto' => $producto]);
                    if(!$almacenProductoNuevo)
                        $almacenProductoNuevo = new AlmacenProducto();

                    $almacenProductoNuevo->setAlmacen($almacenDestino);
                    $almacenProductoNuevo->setProducto($producto);
                    $almacenProductoNuevo->setSaldoDisponible($almacenProductoMovimiento->getCantidad());
                    $almacenProductoNuevo->setSaldoContable($almacenProductoMovimiento->getCantidad());


                    $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
                    $nuevoSaldoContable = $almacenProducto->getSaldoDisponible() - $almacenProductoMovimiento->getCantidad();
                    $almacenProducto->setSaldoContable($nuevoSaldoContable);
                    $almacenProducto->setSaldoDisponible($nuevoSaldoContable);

                    $almacenProductoMovimiento->setPrecioCupVigente($producto->getPrecioCompraCup());
                    $almacenProductoMovimiento->setPrecioMlcVigente($producto->getPrecioCompraMlc());

                    $entityManager->getRepository(AlmacenProducto::class)->add($almacenProducto, $this->getUser(), true);
                    $entityManager->getRepository(AlmacenProducto::class)->add($almacenProductoNuevo, $this->getUser(), true);
                    $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacenProductoMovimiento, $this->getUser(), true);
                }

                break;
            }
            case 'AJT':
                {
                    // Se descuenta del almacen, la cantidad de cada producto
                    foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                        $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
                        $nuevoSaldoContable = $almacenProductoMovimiento->getCantidad();
                        $almacenProducto->setSaldoContable($nuevoSaldoContable);
                        $almacenProducto->setSaldoDisponible($nuevoSaldoContable);
                        $producto = $almacenProducto->getProducto();
                        $almacenProductoMovimiento->setPrecioCupVigente($producto->getPrecioCompraCup());
                        $almacenProductoMovimiento->setPrecioMlcVigente($producto->getPrecioCompraMlc());

                        $entityManager->getRepository(AlmacenProducto::class)->add($almacenProducto, $this->getUser(), true);
                        $entityManager->getRepository(AlmacenProductoMovimiento::class)->add($almacenProductoMovimiento, $this->getUser(), true);
                    }
                    break;
                }
            default: {
                break;
            }
        }

        $movimiento->setEstado('Confirmado');
        $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(), true);

        $movimientoEstado = new MovimientoEstado($movimiento, 'Confirmado');
        $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);
        return $movimiento;
    }

    /**
     * @Route("/{id}/cancelar", name="app_movimiento_cancel", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTOR")
     */
    public function cancelar(Movimiento $movimiento, EntityManagerInterface $entityManager): Response
    {
        // Se actualizan los saldos de los productos entrados en el almacen destino, depende del tipo de movimiento
        $tipoMovimiento = substr($movimiento->getCodigo(),0,3);
        switch ($tipoMovimiento){
            case 'VEN':
            case 'DEV':
            case 'GAP':
                {
                    // Se reponen en almacen almacen la cantidad de cada producto
                    foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                        $almacenProducto = $almacenProductoMovimiento->getAlmacenProducto();
                        $nuevoSaldoDisponible = $almacenProducto->getSaldoDisponible() + $almacenProductoMovimiento->getCantidad();
                        $almacenProducto->setSaldoDisponible($nuevoSaldoDisponible);
                        $entityManager->getRepository(AlmacenProducto::class)->add($almacenProducto, $this->getUser(), true);
                    }
                    break;
                }
        }
        $movimiento->setEstado('Cancelado');
        $entityManager->getRepository(Movimiento::class)->add($movimiento, $this->getUser(), true);

        $movimientoEstado = new MovimientoEstado($movimiento, 'Cancelado');
        $entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $this->getUser(), true);

        $this->notificacionSatisfactoria('Movimiento: ' . $movimiento->getCodigo() . ' cancelado satisfactoriamente');
        return $this->redirectToReferer();
    }

    /**
     * @Route("/{id}", name="app_movimiento_show", methods={"GET"})
     */
    public function show(Movimiento $movimiento, MovimientoRepository $movimientoRepository): Response
    {
        $tipoMovimiento = substr($movimiento->getCodigo(),0,3);
        $importeTotalCup = $importeTotalMlc = 0;

        switch ($tipoMovimiento){
            case 'ENT': {
                $tipoMovimientoLabel = 'Entrada';
                $clienteProveedor = 'Proveedor';

                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento){
                    $precioCupVigente = $productoMovimiento->getPrecioCupVigente() ? $productoMovimiento->getPrecioCupVigente() : $productoMovimiento->getProducto()->getPrecioCompraCup();
                    $precioMlcVigente = $productoMovimiento->getPrecioMlcVigente() ? $productoMovimiento->getPrecioMlcVigente() : $productoMovimiento->getProducto()->getPrecioCompraMlc();

                    $importeTotalCup += $productoMovimiento->getCantidad() * $precioCupVigente;
                    $importeTotalMlc += $productoMovimiento->getCantidad() * $precioMlcVigente;
                }
                break;
            }
            case 'RET': {
                $tipoMovimientoLabel = 'Retorno de productos';
                $clienteProveedor = 'Proveedor';
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento){
                    $precioCupVigente = $almacenProductoMovimiento->getPrecioCupVigente() ? $almacenProductoMovimiento->getPrecioCupVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup();
                    $precioMlcVigente = $almacenProductoMovimiento->getPrecioMlcVigente() ? $almacenProductoMovimiento->getPrecioMlcVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc();

                    $importeTotalCup += $almacenProductoMovimiento->getCantidad() * $precioCupVigente;
                    $importeTotalMlc += $almacenProductoMovimiento->getCantidad() * $precioMlcVigente;
                }
                break;
            }
            case 'VEN': {
                $tipoMovimientoLabel = 'Venta';
                $clienteProveedor = 'Cliente';
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento){
                    $precioCupVigente = $almacenProductoMovimiento->getPrecioCupVigente() ? $almacenProductoMovimiento->getPrecioCupVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup();
                    $precioMlcVigente = $almacenProductoMovimiento->getPrecioMlcVigente() ? $almacenProductoMovimiento->getPrecioMlcVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc();
                    $importeTotalCup += $almacenProductoMovimiento->getCantidad() * $precioCupVigente;
                    $importeTotalMlc += $almacenProductoMovimiento->getCantidad() * $precioMlcVigente;
                }
                break;
            }
            case 'DEV': {
                $tipoMovimientoLabel = 'Devolución a cliente';
                $clienteProveedor = 'Cliente';
                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento){
                    $precioCupVigente = $productoMovimiento->getPrecioCupVigente() ? $productoMovimiento->getPrecioCupVigente() : $productoMovimiento->getProducto()->getPrecioCompraCup();
                    $precioMlcVigente = $productoMovimiento->getPrecioMlcVigente() ? $productoMovimiento->getPrecioMlcVigente() : $productoMovimiento->getProducto()->getPrecioCompraMlc();

                    $importeTotalCup += $productoMovimiento->getCantidad() * $precioCupVigente;
                    $importeTotalMlc += $productoMovimiento->getCantidad() * $precioMlcVigente;
                }
                break;
            }
            case 'GAP': {
                $tipoMovimientoLabel = 'Gasto de aporte';
                $clienteProveedor = 'Cliente';
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento){
                    $precioCupVigente = $almacenProductoMovimiento->getPrecioCupVigente() ? $almacenProductoMovimiento->getPrecioCupVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraCup();
                    $precioMlcVigente = $almacenProductoMovimiento->getPrecioMlcVigente() ? $almacenProductoMovimiento->getPrecioMlcVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraMlc();

                    $importeTotalCup += $almacenProductoMovimiento->getCantidad() * $precioCupVigente;
                    $importeTotalMlc += $almacenProductoMovimiento->getCantidad() * $precioMlcVigente;
                }
                break;
            }
            case 'TRF': {
                $tipoMovimientoLabel = 'Transferencia entre almacenes';
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento){
                    $precioCupVigente = $almacenProductoMovimiento->getPrecioCupVigente() ? $almacenProductoMovimiento->getPrecioCupVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraCup();
                    $precioMlcVigente = $almacenProductoMovimiento->getPrecioMlcVigente() ? $almacenProductoMovimiento->getPrecioMlcVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraMlc();

                    $importeTotalCup += $almacenProductoMovimiento->getCantidad() * $precioCupVigente;
                    $importeTotalMlc += $almacenProductoMovimiento->getCantidad() * $precioMlcVigente;
                }
                break;
            }
            case 'AJT': {
                $tipoMovimientoLabel = 'Ajuste de inventario';
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento){
                    $precioCupVigente = $almacenProductoMovimiento->getPrecioCupVigente() ? $almacenProductoMovimiento->getPrecioCupVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraCup();
                    $precioMlcVigente = $almacenProductoMovimiento->getPrecioMlcVigente() ? $almacenProductoMovimiento->getPrecioMlcVigente() : $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraMlc();

                    $importeTotalCup += $almacenProductoMovimiento->getCantidad() * $precioCupVigente;
                    $importeTotalMlc += $almacenProductoMovimiento->getCantidad() * $precioMlcVigente;
                }
                break;
            }
            default: {
                $tipoMovimientoLabel = 'N/E';
                $clienteProveedor = null;
                $importeTotalCup = 0;
                $importeTotalMlc = 0;
                break;
            }
        }

        return $this->render('movimiento/show.html.twig', [
            'movimiento' => $movimiento,
            'tipo_movimiento_label' => $tipoMovimientoLabel,
            'tipo_movimiento' => $tipoMovimiento,
            'importe_total_cup' => $importeTotalCup,
            'importe_total_mlc' => $importeTotalMlc,
            'cliente_proveedor' => isset($clienteProveedor) ? $clienteProveedor : null,
            'movimiento_anterior' => $movimientoRepository->findMovimiento('anterior', $tipoMovimiento, $movimiento->getId()),
            'movimiento_siguiente' => $movimientoRepository->findMovimiento('siguiente', $tipoMovimiento, $movimiento->getId()),
        ]);
    }

    /**
     * @Route("/{id}/exportar/{formato}", name="app_movimiento_exportar", methods={"GET", "POST"})
     */
    public function exportar(Movimiento $movimiento, EntityManagerInterface $entityManager, string $formato = 'pdf'): Response
    {
//        try{
        return $this->exportarMovimiento($movimiento, $entityManager, $formato);
//        } catch (\Exception $e){
//            if($this->isGranted('ROLE_ADMINISTRADOR_SISTEMA')){
//                $this->notificacionExcepcion($e->getMessage());
//            }
//            $this->notificacionError('No se pudo realizar la exportación. Ha ocurrido un error inesperado');
//            return $this->redirectToRoute('app_main');
//        }
    }

    /**
     * @Route("/{id}/exportar/pdf", name="app_movimiento_exportar_pdf", methods={"GET", "POST"})
     */
    public function exportarPdf(Movimiento $movimiento, EntityManagerInterface $entityManager): Response
    {

        $converter = new OfficeConverter('temp_file.doc', null, '"C:\Program Files\LibreOffice\program\soffice.exe"');
        $converter->convertTo('output-file.pdf'); //generates pdf file in same directory as test-file.docx
    }

    private function exportarMovimiento(Movimiento $movimiento, EntityManagerInterface $entityManager, string $formato){
        $miEmpresa = $entityManager->getRepository(Organizacion::class)->findOneBy(['esMiOrganizacion' => true]);
        $tipoMovimiento = substr($movimiento->getCodigo(),0,3);
        $tipoClienteProveedor = in_array($tipoMovimiento, ['ENT', 'DEV']) ? 'Proveedor' : 'Cliente';

//        switch ($tipoMovimiento){
//            case ''
//        }
        $templateProcessor = new TemplateProcessor('assets/templates/movimientos_template.docx');

        $templateProcessor->setValue('tipoClienteProveedor', $tipoClienteProveedor);

        $templateProcessor->setValue('empresaNombre', $miEmpresa->getNombre() ?: '-');
        $templateProcessor->setValue('empresaDomicilio', $miEmpresa->getDomicilio() ?: '-');
        $templateProcessor->setValue('empresaTelefonos', $miEmpresa->getTelefonos() ?: '-');
        $templateProcessor->setValue('empresaCorreoElectronico', $miEmpresa->getCorreosElectronicos() ?: '-');

        $clienteProveedor = $movimiento->getEmpresa();
        if($clienteProveedor){
            $templateProcessor->setValue('clienteProveedorNombre', $clienteProveedor && $clienteProveedor->getNombre() ?: '-');
            $templateProcessor->setValue('clienteProveedorDomicilio', $clienteProveedor->getDomicilio() ?: '-');
            $templateProcessor->setValue('clienteProveedorTelefonos', $clienteProveedor->getTelefonos() ?: '-');
            $templateProcessor->setValue('clienteProveedorCorreosElectronicos', $clienteProveedor->getCorreosElectronicos() ?: '-');
            $templateProcessor->setValue('clienteProveedorPersonaContacto', $clienteProveedor->getContactoPrincipal() ?: '-');
            $templateProcessor->setValue('clienteProveedorCuentaCUP', $clienteProveedor->getCuentaCup() ?: '-');
            $templateProcessor->setValue('clienteProveedorCuentaMLC', $clienteProveedor->getCuentaMlc() ?: '-');
        } else {
            $templateProcessor->setValue('clienteProveedorNombre', '-');
            $templateProcessor->setValue('clienteProveedorDomicilio', '-');
            $templateProcessor->setValue('clienteProveedorTelefonos', '-');
            $templateProcessor->setValue('clienteProveedorCorreosElectronicos', '-');
            $templateProcessor->setValue('clienteProveedorPersonaContacto', '-');
            $templateProcessor->setValue('clienteProveedorCuentaCUP', '-');
            $templateProcessor->setValue('clienteProveedorCuentaMLC',  '-');
        }
        $templateProcessor->setValue('codigo', $movimiento->getCodigo());
        $templateProcessor->setValue('movimientoDescripcion', $movimiento->getDescripcion());

        $templateProcessor->setValue('creadoPor', $movimiento->getCreadoPor()->getNombreCompleto());
        $templateProcessor->setValue('creadoEn', $movimiento->getCreadoEn()->format('d-m-Y h:i:s a'));
        $templateProcessor->setValue('fecha', $movimiento->getFecha()->format('d-m-Y'));
        $templateProcessor->setValue('fechaActual', date('d-m-Y h:i:s a'));

        //

        $templateProcessor->setValue('entregadoPorNombre', $movimiento->getEntregadoPorNombre() ?: '');
        $templateProcessor->setValue('entregadoPorCI', $movimiento->getEntregadoPorCI() ?: '');
        $templateProcessor->setValue('entregadoPorCargo', $movimiento->getEntregadoPorCargo() ?: '');
        $templateProcessor->setValue('entregadoPorFecha', $movimiento->getEntregadoPorFecha() ?: '');

        $templateProcessor->setValue('transportadoPorNombre', $movimiento->getTransportadoPorNombre() ?: '');
        $templateProcessor->setValue('transportadoPorCI', $movimiento->getTransportadoPorCI() ?: '');
        $templateProcessor->setValue('transportadoPorCargo', $movimiento->getTransportadoPorCargo() ?: '');
        $templateProcessor->setValue('transportadoPorFecha', $movimiento->getTransportadoPorFecha() ?: '');

        $templateProcessor->setValue('recibidoPorNombre', $movimiento->getRecibidoPorNombre() ?: '');
        $templateProcessor->setValue('recibidoPorCI', $movimiento->getRecibidoPorCI() ?: '');
        $templateProcessor->setValue('recibidoPorCargo', $movimiento->getRecibidoPorCargo() ?: '');
        $templateProcessor->setValue('recibidoPorFecha', $movimiento->getRecibidoPorFecha() ?: '');

        switch ($tipoMovimiento){
            case 'ENT': { // Entrada / Precio compra
                $templateProcessor->cloneRow('i', count($movimiento->getProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Entrada');

                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $productoMovimiento->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $productoMovimiento->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $productoMovimiento->getCantidad());

                    $precioCup = $productoMovimiento->getProducto()->getPrecioCompraCup();
                    $precioMlc = $productoMovimiento->getProducto()->getPrecioCompraMlc();

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($precioCup, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count,number_format($precioMlc, 2, '.', ''));


                    $pImpCUP = $precioCup * $productoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $precioMlc * $productoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));

                break;
            }
            case 'VEN': { // Salida / Precio venta
                $templateProcessor->cloneRow('i', count($movimiento->getAlmacenProductoMovimientos()));

                if($movimiento->getEstado() === 'Sin confirmar'){
                    $templateProcessor->setValue('documento', 'Venta');
                } else {
                    $templateProcessor->setValue('documento', 'Factura');
                }
                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $almacenProductoMovimiento->getCantidad());
                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup(), 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc(), 2, '.', ''));

                    $pImpCUP = $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup() * $almacenProductoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc() * $almacenProductoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));
                break;
            }
            case 'RET': { // Entrada / Precio de venta
                $templateProcessor->cloneRow('i', count($movimiento->getProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Retorno');

                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $productoMovimiento->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $productoMovimiento->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $productoMovimiento->getCantidad());

                    $precioCup = $productoMovimiento->getProducto()->getPrecioVentaCup();
                    $precioMlc = $productoMovimiento->getProducto()->getPrecioVentaMlc();

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($precioCup, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($precioMlc, 2, '.', ''));


                    $pImpCUP = $precioCup * $productoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $precioMlc * $productoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));
                break;
            }
            case 'DEV': { // Salida / Precio de compra
                $templateProcessor->cloneRow('i', count($movimiento->getProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Devolución');

                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $productoMovimiento->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $productoMovimiento->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $productoMovimiento->getCantidad());

                    $precioCup = $productoMovimiento->getProducto()->getPrecioCompraCup();
                    $precioMlc = $productoMovimiento->getProducto()->getPrecioCompraMlc();

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($precioCup, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($precioMlc, 2, '.', ''));


                    $pImpCUP = $precioCup * $productoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $precioMlc * $productoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));

                break;
            }
            case 'GAP': { // Salida / Precio 0
                $templateProcessor->cloneRow('i', count($movimiento->getAlmacenProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Gasto de aporte');
                $count = 1;

                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $almacenProductoMovimiento->getCantidad());
//                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraCup(), 2, '.', ''));
//                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraMlc(), 2, '.', ''));

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format(0, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format(0, 2, '.', ''));

                    $pImpCUP = 0;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = 0;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }
                $templateProcessor->setValue('totalCUP', number_format(0, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format(0, 2, '.', ''));
                break;
            }
            case 'AJT': { // Salida
                $templateProcessor->cloneRow('i', count($movimiento->getAlmacenProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Gasto de aporte');
                $count = 1;

                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $almacenProductoMovimiento->getCantidad());
//                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraCup(), 2, '.', ''));
//                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioCompraMlc(), 2, '.', ''));

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format(0, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format(0, 2, '.', ''));

                    $pImpCUP = 0;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = 0;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }
                $templateProcessor->setValue('totalCUP', number_format(0, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format(0, 2, '.', ''));
                break;
            }
            default: {
                $this->notificacionError('No se pudo realizar la exportación. Ha ocurrido un error inesperado');
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()]);
            }
        }


        //
        $rendererName = Settings::PDF_RENDERER_DOMPDF;
        $rendererLibraryPath = realpath('../vendor/dompdf/dompdf');
        Settings::setPdfRenderer($rendererName, $rendererLibraryPath);


        $temp_file = sys_get_temp_dir() . '\\' . $movimiento->getCodigo() . '.docx';
        $templateProcessor->saveAs($temp_file);

        if($formato === 'word'){
            $response = new BinaryFileResponse($temp_file);
            $response->headers->set('Content-Type', 'application/msword');
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                sprintf('%s.docx', $movimiento->getCodigo())
            );
        } else {
            $temp_file_pdf = $movimiento->getCodigo() . '.pdf';

            $converter = new OfficeConverter($temp_file, null, '"C:\Program Files\LibreOffice\program\soffice.exe"');
            $converter->convertTo($temp_file_pdf);

            $response = new BinaryFileResponse(sys_get_temp_dir() . '\\' . $temp_file_pdf);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                sprintf('%s.pdf', $movimiento->getCodigo())
            );
        }

        return $response;
    }

    private function exportarMovimientoAUX(Movimiento $movimiento, EntityManagerInterface $entityManager){
        $miEmpresa = $entityManager->getRepository(Organizacion::class)->findOneBy(['esMiOrganizacion' => true]);
        $tipoMovimiento = substr($movimiento->getCodigo(),0,3);
        $tipoClienteProveedor = in_array($tipoMovimiento, ['ENT', 'DEV']) ? 'Proveedor' : 'Cliente';

        $templateProcessor = new TemplateProcessor('assets/templates/movimientos.docx');

        $templateProcessor->setValue('tipoClienteProveedor', $tipoClienteProveedor);

        $templateProcessor->setValue('empresaNombre', $miEmpresa->getNombre() ?: '-');
        $templateProcessor->setValue('empresaDomicilio', $miEmpresa->getDomicilio() ?: '-');
        $templateProcessor->setValue('empresaTelefonos', $miEmpresa->getTelefonos() ?: '-');
        $templateProcessor->setValue('empresaCorreoElectronico', $miEmpresa->getCorreosElectronicos() ?: '-');

        $clienteProveedor = $movimiento->getEmpresa();
        if($clienteProveedor){
            $templateProcessor->setValue('clienteProveedorNombre', $clienteProveedor && $clienteProveedor->getNombre() ?: '-');
            $templateProcessor->setValue('clienteProveedorDomicilio', $clienteProveedor->getDomicilio() ?: '-');
            $templateProcessor->setValue('clienteProveedorTelefonos', $clienteProveedor->getTelefonos() ?: '-');
            $templateProcessor->setValue('clienteProveedorCorreosElectronicos', $clienteProveedor->getCorreosElectronicos() ?: '-');
            $templateProcessor->setValue('clienteProveedorPersonaContacto', $clienteProveedor->getContactoPrincipal() ?: '-');
            $templateProcessor->setValue('clienteProveedorCuentaCUP', $clienteProveedor->getCuentaCup() ?: '-');
            $templateProcessor->setValue('clienteProveedorCuentaMLC', $clienteProveedor->getCuentaMlc() ?: '-');
        } else {
            $templateProcessor->setValue('clienteProveedorNombre', '-');
            $templateProcessor->setValue('clienteProveedorDomicilio', '-');
            $templateProcessor->setValue('clienteProveedorTelefonos', '-');
            $templateProcessor->setValue('clienteProveedorCorreosElectronicos', '-');
            $templateProcessor->setValue('clienteProveedorPersonaContacto', '-');
            $templateProcessor->setValue('clienteProveedorCuentaCUP', '-');
            $templateProcessor->setValue('clienteProveedorCuentaMLC',  '-');
        }
        $templateProcessor->setValue('codigo', $movimiento->getCodigo());
        $templateProcessor->setValue('movimientoDescripcion', $movimiento->getDescripcion());

        $templateProcessor->setValue('creadoPor', $movimiento->getCreadoPor()->getNombreCompleto());
        $templateProcessor->setValue('creadoEn', $movimiento->getCreadoEn()->format('d-m-Y h:i:s a'));
        $templateProcessor->setValue('fecha', $movimiento->getFecha()->format('d-m-Y'));
        $templateProcessor->setValue('fechaActual', date('d-m-Y h:i:s a'));

        //

        $templateProcessor->setValue('entregadoPorNombre', $movimiento->getEntregadoPorNombre() ?: '');
        $templateProcessor->setValue('entregadoPorCI', $movimiento->getEntregadoPorCI() ?: '');
        $templateProcessor->setValue('entregadoPorCargo', $movimiento->getEntregadoPorCargo() ?: '');
        $templateProcessor->setValue('entregadoPorFecha', $movimiento->getEntregadoPorFecha() ?: '');

        $templateProcessor->setValue('transportadoPorNombre', $movimiento->getTransportadoPorNombre() ?: '');
        $templateProcessor->setValue('transportadoPorCI', $movimiento->getTransportadoPorCI() ?: '');
        $templateProcessor->setValue('transportadoPorCargo', $movimiento->getTransportadoPorCargo() ?: '');
        $templateProcessor->setValue('transportadoPorFecha', $movimiento->getTransportadoPorFecha() ?: '');

        $templateProcessor->setValue('recibidoPorNombre', $movimiento->getRecibidoPorNombre() ?: '');
        $templateProcessor->setValue('recibidoPorCI', $movimiento->getRecibidoPorCI() ?: '');
        $templateProcessor->setValue('recibidoPorCargo', $movimiento->getRecibidoPorCargo() ?: '');
        $templateProcessor->setValue('recibidoPorFecha', $movimiento->getRecibidoPorFecha() ?: '');

        switch ($tipoMovimiento){
            case 'ENT': { // Entrada / Precio compra
                $templateProcessor->cloneRow('i', count($movimiento->getProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Entrada');

                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $productoMovimiento->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $productoMovimiento->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $productoMovimiento->getCantidad());

                    $precioCup = $productoMovimiento->getProducto()->getPrecioCompraCup();
                    $precioMlc = $productoMovimiento->getProducto()->getPrecioCompraMlc();

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($precioCup, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count,number_format($precioMlc, 2, '.', ''));


                    $pImpCUP = $precioCup * $productoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $precioMlc * $productoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));

                break;
            }
            case 'VEN': { // Salida / Precio venta
                $templateProcessor->cloneRow('i', count($movimiento->getAlmacenProductoMovimientos()));

                if($movimiento->getEstado() === 'Sin confirmar'){
                    $templateProcessor->setValue('documento', 'Venta');
                } else {
                    $templateProcessor->setValue('documento', 'Factura');
                }
                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getAlmacenProductoMovimientos() as $almacenProductoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $almacenProductoMovimiento->getCantidad());
                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup(),2,'.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc(),2,'.', ''));

                    $pImpCUP = $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaCup() * $almacenProductoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $almacenProductoMovimiento->getAlmacenProducto()->getProducto()->getPrecioVentaMlc() * $almacenProductoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));
                break;
            }
            case 'RET': { // Entrada / Precio de venta
                $templateProcessor->cloneRow('i', count($movimiento->getProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Retorno');

                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $productoMovimiento->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $productoMovimiento->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $productoMovimiento->getCantidad());

                    $precioCup = $productoMovimiento->getProducto()->getPrecioVentaCup();
                    $precioMlc = $productoMovimiento->getProducto()->getPrecioVentaMlc();

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($precioCup, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($precioMlc, 2, '.', ''));


                    $pImpCUP = $precioCup * $productoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $precioMlc * $productoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));
                break;
            }
            case 'DEV': { // Salida / Precio de compra
                $templateProcessor->cloneRow('i', count($movimiento->getProductoMovimientos()));
                $templateProcessor->setValue('documento', 'Devolución');

                $count = 1;
                $subTotalCUP = 0;
                $subTotalMLC = 0;
                foreach ($movimiento->getProductoMovimientos() as $productoMovimiento) {
                    $templateProcessor->setValue('i#' . $count, $count);
                    $templateProcessor->setValue('pNombre#' . $count, $productoMovimiento->getProducto()->getNombre());
                    $templateProcessor->setValue('pUM#' . $count, $productoMovimiento->getProducto()->getUnidadMedida());
                    $templateProcessor->setValue('pCant#' . $count, $productoMovimiento->getCantidad());

                    $precioCup = $productoMovimiento->getProducto()->getPrecioCompraCup();
                    $precioMlc = $productoMovimiento->getProducto()->getPrecioCompraMlc();

                    $templateProcessor->setValue('pPrecCUP#' . $count, number_format($precioCup, 2, '.', ''));
                    $templateProcessor->setValue('pPrecMLC#' . $count, number_format($precioMlc, 2, '.', ''));


                    $pImpCUP = $precioCup * $productoMovimiento->getCantidad();
                    $subTotalCUP += $pImpCUP;
                    $templateProcessor->setValue('pImpCUP#' . $count, number_format($pImpCUP, 2, '.', ''));

                    $pImpMLC = $precioMlc * $productoMovimiento->getCantidad();
                    $subTotalMLC += $pImpMLC;
                    $templateProcessor->setValue('pImpMLC#' . $count, number_format($pImpMLC, 2, '.', ''));
                    $count++;
                }

                $taxCUP = 0;
                $taxMLC = 0;
                $templateProcessor->setValue('subTCUP', number_format($subTotalCUP, 2, '.', ''));
                $templateProcessor->setValue('taxCUP', number_format($taxCUP, 2, '.', ''));
                $templateProcessor->setValue('totalCUP', number_format($subTotalCUP + $taxCUP, 2, '.', ''));

                $templateProcessor->setValue('subTMLC', number_format($subTotalMLC, 2, '.', ''));
                $templateProcessor->setValue('taxMLC', number_format($taxMLC, 2, '.', ''));
                $templateProcessor->setValue('totalMLC', number_format($subTotalMLC + $taxMLC, 2, '.', ''));

                break;
            }
            default: {
                $this->notificacionError('No se pudo realizar la exportación. Ha ocurrido un error inesperado');
                return $this->redirectToRoute('app_movimiento_show', ['id' => $movimiento->getId()]);
            }
        }


        //
        $rendererName = Settings::PDF_RENDERER_DOMPDF;
        $rendererLibraryPath = realpath('../vendor/dompdf/dompdf');
        Settings::setPdfRenderer($rendererName, $rendererLibraryPath);


        $temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');
        $temp_file = "a.docx";
        $templateProcessor->saveAs($temp_file);
        $converter = new OfficeConverter($temp_file, null, 'soffice', false);
        $converter->convertTo('samypdf.pdf');
        dd($converter);

        dd($temp_file);





        $phpWord = IOFactory::load($temp_file);
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');


        $temp_file_pdf = tempnam(sys_get_temp_dir(), 'PDF');
        $xmlWriter->save($temp_file_pdf);


//        $phpWord->save('a.pdf','DomPDF');




//        $phpWord = \PhpOffice\PhpWord\IOFactory::load($temp_file);
//
//        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord , 'PDF');
//        $temp_file_pdf = tempnam(sys_get_temp_dir(), 'PDF');
//        $xmlWriter->save($temp_file_pdf);

//        $response = new BinaryFileResponse($temp_file);
//        $response->headers->set('Content-Type', 'application/msword');
//        $response->setContentDisposition(
//            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
//            sprintf('%s.doc', $movimiento->getCodigo())
//        );
//
        $response = new BinaryFileResponse($temp_file_pdf);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('%s.pdf', $movimiento->getCodigo())
        );

        return $response;
    }

    /**
     * @Route("/{id}", name="app_movimiento_delete", methods={"POST"})
     * @IsGranted("ROLE_GESTOR")
     */
    public function delete(Request $request, Movimiento $movimiento, MovimientoRepository $movimientoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$movimiento->getId(), $request->request->get('_token'))) {
            $movimientoRepository->remove($movimiento, true);
        }

        return $this->redirectToRoute('app_movimiento_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/acciones", name="app_movimiento_acciones_grupales", priority="20", methods={"POST"})
     */
    public function acciones(Request $request): Response
    {
        dd($request->request->all());

        return $this->redirectToRoute('app_movimiento_index', [], Response::HTTP_SEE_OTHER);
    }

}
