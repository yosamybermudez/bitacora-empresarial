<?php

namespace App\Controller;

use AppBase\Entity\Empresa;
use AppSistema\Entity\VariableConfiguracion;
use BackupManager\Compressors\CompressorProvider;
use BackupManager\Compressors\GzipCompressor;
use BackupManager\Compressors\NullCompressor;
use BackupManager\Config\Config;
use BackupManager\Databases\DatabaseProvider;
use BackupManager\Databases\MysqlDatabase;
use BackupManager\Databases\PostgresqlDatabase;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Filesystems\LocalFilesystem;
use BackupManager\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * @Route("/")
 */
class MainController extends AppController
{
    /**
     * @Route("/", name="app_main_index")
     */
    public function index(ChartBuilderInterface $chartBuilder, MovimientoRepository $movimientoRepository, RoleHierarchyInterface $roleHierarchy): Response
    {
        try{
            if(!$this->getUser()){
                return $this->render('_themes/metroui/loading.html.twig', [

                ]);
            }
        } catch (\Exception $e){
            return $this->redirectToRoute('app_security_login');
        }

        $frecuencia = 'anno';

        $movimientosTipoData = $this->movimientosPorTipoChartData($movimientoRepository, $frecuencia);

        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $chart->setData($movimientosTipoData);

        $chart->setOptions([
//            'scales' => [
//                'y' => [
//                    'suggestedMin' => 0,
//                    'suggestedMax' => 100,
//                ],
//            ],
            'maintainAspectRatio' => false
        ]);

        return $this->render('principal/index.html.twig', [
            'chart' => $chart
        ]);
    }

    /**
     * @Route("/login", name="app_security_login", methods={"GET", "POST"})
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
        $miEmpresa = null;
        $instalar = false;
        $license  = null;
        try{
            $isInstalled = $entityManager->getRepository(VariableConfiguracion::class)->findOneByNombre('app_installed');
            dd($isInstalled);
            if(!$isInstalled){
                $instalar = true;
            } else {
                $miEmpresa = $entityManager->getRepository(Empresa::class)->findOneBy(['esMiOrganizacion' => true]);
                if($this->getLicense()['status'] !== 'valid'){
                    if($request->query->get('extra') === null){
                        return $this->redirectToRoute('app_login_license');
                    }
                }
            }

            $license = $this->getLicense();
        } catch (\Exception $e){
            dd($e);
            $instalar = true;
        }



        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();



        if($license){

            $remaining = date_diff($license['end_date'],$license['starts']);
//        $days_left = $remaining->days;
            $license['days_left'] = $remaining->days;
//        dump(($license));
//        $remaining = str_replace('  ', '',
//            implode(' ', [
//                $this->remaining($remaining->y, 'y'),
//                $this->remaining($remaining->m, 'm'),
//                $this->remaining($remaining->d, 'd'),
//            ])
//        );
        }

        return $this->renderForm('app/security/login.html.twig', [
            'last_username' => $lastUsername,
            'license' => $license,
            'error' => $error,
            'instalar' => $instalar,
            'mi_empresa' => $miEmpresa,
        ]);
    }

    /**
     * @Route("/error_404", name="_error_404", methods="GET")
     */
    public function error404()
    {
        return $this->render('_themes/metroui/base_error_404.html.twig');
    }

    private function getBackupManager(){
        $filesystems = new FilesystemProvider(Config::fromPhpFile(realpath('../vendor/backup-manager/backup-manager/config/storage.php')));
        $filesystems->add(new LocalFilesystem());

        $databases = new DatabaseProvider(Config::fromPhpFile(realpath('../vendor/backup-manager/backup-manager/config/database.php')));
        $databases->add(new MysqlDatabase());
        $databases->add(new PostgresqlDatabase());

        $compressors = new CompressorProvider();
        $compressors->add(new GzipCompressor());
        $compressors->add(new NullCompressor());

// build manager
        return new Manager($filesystems, $databases, $compressors);
    }

    /**
     * @Route("/database/backup_restore", name="app_database_backup_restore", methods={"GET","POST"})
     */
    public function databaseBackupRestore(Request $request) : Response {

        if($request->getMethod() === 'POST'){
            if($request->request->get('backup_database') === ''){
                try{
                    $return_var = NULL;
                    $output = NULL;
                    $file = 'bitacora_backup_' . date('YmdHis') .  ".sql";
                    $tempFile = tempnam(sys_get_temp_dir(), $file);

                    $command = "mysqldump -u databases -h 127.0.0.1 -pD4t4b4s3s bitacora > " . $tempFile;
                    exec($command, $output, $return_var);

                    $response = new BinaryFileResponse($tempFile);
                    $response->headers->set('Content-Type', 'application/sql');
                    $response->setContentDisposition(
                        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        sprintf($file)
                    );
                    return $response;

                }
                catch (\Exception $e){
                    $this->notificacionExcepcion($e->getMessage());
                    $this->notificacionError('Ocurrió un error al salvar la base de datos');
                }
            }

            if($request->request->get('restore_database') === ''){
                try{
                    $file = $request->request->get('restore_database_file');
                    $return_var = NULL;
                    $output = NULL;

                    $command = "mysql -u databases -h 127.0.0.1 -pD4t4b4s3s bitacora < " . $file;
                    exec($command, $output, $return_var);

                    $this->notificacionSatisfactoria('La base de datos fue restaurada satisfactoriamente');
                    return $this->redirectToRoute('app_database_backup_restore');

                }
                catch (\Exception $e){
                    dd($e);
                    $this->notificacionExcepcion($e->getMessage());
                    $this->notificacionError('Ocurrió un error al restaurar la base de datos');
                }
            }
        }
//        try{
//            $return_var = NULL;
//            $output = NULL;
//            $file = 'bitacora_backup_' . date('YmdHis') .  ".sql";
//            $tempFile = tempnam(sys_get_temp_dir(), $file);
//
//            $command = "mysqldump -u databases -h 127.0.0.1 -pD4t4b4s3s bitacora > " . $tempFile;
//            exec($command, $output, $return_var);
//
//            $response = new BinaryFileResponse($tempFile);
//            $response->headers->set('Content-Type', 'application/sql');
//            $response->setContentDisposition(
//                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
//                sprintf($file)
//            );
//
//            $this->notificacionSatisfactoria('Base de datos resguardada satisfactoriamente');
//            return $response;
//
//        }
//        catch (\Exception $e){
//            $this->notificacionExcepcion($e->getMessage());
//            $this->notificacionError('Ocurrió un error al salvar la base de datos');
//        }
//        return $this->redirectToReferer();
        return $this->render('app/security/backup_restore.html.twig', [

        ]);
    }

    public function obtenerEnlaces(string $module_id = null)
    {
        $sidebar = [];
        $enlaces = [];

        //MODULOS
        $modulos_activos = $this->entityManager->getRepository(SistemaModulo::class)->findByActivado(true);
        $enlaces[] = new ModuloEnlace('', 'Inicio', 'mif-home', null, []);
        $modulos = new ModuloEnlace('', 'Módulos', 'mif-apps', null, []);
        $modulo_seleccionado = $this->entityManager->getRepository(SistemaModulo::class)->findOneByIdentificador($module_id);
        foreach ($modulos_activos as $modulo){
            $mod = new ModuloEnlace($this->generateUrl('app_sistema_modulo_show', ['id' => $modulo->getIdentificador()]), $modulo->getNombre(), 'mif-chevron-right', null, [], []); // Verificar los roles
            $mod->setEsModulo();
            $modulos->addSubmodulo(
                $mod
            );
        }
        $enlaces[] = $modulos;

        $sidebar[] = [
            'titulo' => 'Tablero',
            'enlaces' => $enlaces,
        ];

        $enlaces = [];

        switch ($module_id){
            case 'rec_humanos' : {

                break;
            }
            case 'inventario' : {
                //MENU ALMACEN
//                if($this->rolInRolHierarchy('ROLE_GESTOR')){
//                    $enlaces = [];
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_producto_new'), 'Nuevo producto', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_almacen_new'), 'Nuevo almacén', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_ajuste_inventario_new'), 'Nuevo ajuste de inventario', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_transferencia_almacen_new'), 'Nueva transferencia (almacenes)', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_producto_index'), 'Productos', 'mif-open-book', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_almacen_index'), 'Almacenes', 'mif-open-book', null, ['ROLE_GESTOR'], []);
//
//                    $tipo = 'ajustes';
//                    $enlaces[] = new ModuloEnlace('', 'Resumen de ' . $tipo . ' de inventario', 'mif-open-book', null, [], [
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia', 'tipo' => $tipo]), 'Resumen diario', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes', 'tipo' => $tipo]), 'Resumen mensual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno', 'tipo' => $tipo]), 'Resumen anual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                    ]);
//
//                    $tipo = 'transferencias';
//                    $enlaces[] = new ModuloEnlace('', 'Resumen de ' . $tipo, 'mif-open-book', null, [], [
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia', 'tipo' => $tipo]), 'Resumen diario', 'mif-chevron-right', null, [], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes', 'tipo' => $tipo]), 'Resumen mensual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno', 'tipo' => $tipo]), 'Resumen anual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                    ]);
//
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_organizacion_index', ['tipo' => 'proveedor']), 'Proveedores', 'mif-organization', null, ['ROLE_GESTOR'], []);
//                }
                break;
            }
            case 'ventas' : {
                ////////////////
                //MENU VENDEDOR
//                if($this->rolInRolHierarchy('ROLE_EDITOR')){
//                    $enlaces = [];
//
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_sistema_modulo_show', ['id' => $module_id]), 'Tablero', 'mif-home', null, ['ROLE_ESTANDAR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_venta_rapida_new'), 'Nueva venta rápida', 'mif-flash-on', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_venta_new'), 'Nueva venta', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_retorno_new'), 'Nuevo retorno', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_gasto_aporte_new'), 'Nuevo gasto de aporte', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_organizacion_new', ['tipo' => 'cliente']), 'Registrar cliente', 'mif-add', null, ['ROLE_GESTOR']);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'ventas', 'fecha' => date('Ymd')]), 'Ventas del día', 'mif-open-book', null, ['ROLE_GESTOR'], []);
//
//                    $tipo = 'ventas';
//                    $enlaces[] = new ModuloEnlace('', 'Resumen de ' . $tipo, 'mif-open-book', null, [], [
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia', 'tipo' => $tipo]), 'Resumen diario', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes', 'tipo' => $tipo]), 'Resumen mensual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno', 'tipo' => $tipo]), 'Resumen anual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                    ]);
//
//                    $tipo = 'retornos';
//                    $enlaces[] = new ModuloEnlace('', 'Resumen de ' . $tipo, 'mif-open-book', null, [], [
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia', 'tipo' => $tipo]), 'Resumen diario', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes', 'tipo' => $tipo]), 'Resumen mensual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno', 'tipo' => $tipo]), 'Resumen anual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                    ]);
//
//                    $tipo_nombre = 'gastos de aporte';
//                    $tipo = $this->filter->minusculasSinEspacioFilter($tipo_nombre);
//                    $enlaces[] = new ModuloEnlace('', 'Resumen de ' . $tipo_nombre, 'mif-open-book', null, [], [
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia', 'tipo' => $tipo]), 'Resumen diario', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes', 'tipo' => $tipo]), 'Resumen mensual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno', 'tipo' => $tipo]), 'Resumen anual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                    ]);
//
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_organizacion_index', ['tipo' => 'cliente']), 'Clientes', 'mif-organization', null, ['ROLE_GESTOR'], []);
//                }
                break;
            }
            case 'compras' : {
                //MENU COMPRADOR
//                if($this->rolInRolHierarchy('ROLE_GESTOR')){
//                    $enlaces = [];
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_entrada_new'), 'Nueva compra / entrada', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_devolucion_new'), 'Nueva devolución', 'mif-add', null, ['ROLE_GESTOR'], []);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_organizacion_new', ['tipo' => 'proveedor']), 'Registrar proveedor', 'mif-add', null, ['ROLE_GESTOR']);
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'entradas', 'fecha' => date('Ymd')]), 'Entradas del día', 'mif-open-book', null, ['ROLE_GESTOR'], []);
//
//                    $tipo = 'entradas';
//                    $enlaces[] = new ModuloEnlace('', 'Resumen de ' . $tipo, 'mif-open-book', null, [], [
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia', 'tipo' => $tipo]), 'Resumen diario', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes', 'tipo' => $tipo]), 'Resumen mensual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno', 'tipo' => $tipo]), 'Resumen anual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                    ]);
//
//                    $tipo = 'devoluciones';
//                    $enlaces[] = new ModuloEnlace('', 'Resumen de ' . $tipo, 'mif-open-book', null, [], [
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia', 'tipo' => $tipo]), 'Resumen diario', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes', 'tipo' => $tipo]), 'Resumen mensual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                        new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno', 'tipo' => $tipo]), 'Resumen anual', 'mif-chevron-right', null, ['ROLE_GESTOR'], []),
//                    ]);
//
//                    $enlaces[] = new ModuloEnlace($this->generateUrl('app_organizacion_index', ['tipo' => 'proveedor']), 'Proveedores', 'mif-organization', null, ['ROLE_GESTOR'], []);
//                }
                break;
            }
            case 'juridica' : {
                break;
            }
            default: break;
        }
        if($modulo_seleccionado){
            $sidebar[] = [
                'titulo' => $modulo_seleccionado->getNombre(),
                'enlaces' => $enlaces,
            ];
        }







//        if($enlaces){
//            $sidebar[] = [
//                'titulo' => 'Operaciones',
//                'enlaces' => $enlaces
//            ];
//        }

        //GESTION

        $enlaces = [];
//
//        if($this->isGranted('ROLE_GESTOR')){
//            $enlaces[] = new ModuloEnlace('', 'Ventas', 'mif-cart', null, [],
//                [
//                    new ModuloEnlace($this->generateUrl('app_organizacion_index', ['tipo' => 'cliente']), 'Clientes', 'mif-chevron-right', $this->generateUrl('app_organizacion_new', ['tipo' => 'cliente'])),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'ventas']), 'Listado de ventas', 'mif-chevron-right', $this->generateUrl('app_movimiento_venta_new')),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'retornos']), 'Retornos de clientes', 'mif-chevron-right', $this->generateUrl('app_movimiento_retorno_new')),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'gastosDeAporte']), 'Gastos de aporte', 'mif-chevron-right', $this->generateUrl('app_movimiento_gasto_aporte_new')),
//                ]);
//        }
//
//        if($this->isGranted('ROLE_GESTOR')) {
//            $enlaces[] = new ModuloEnlace('', 'Compras', 'mif-shopping-basket', null, [],
//                [
//                    new ModuloEnlace($this->generateUrl('app_organizacion_index', ['tipo' => 'proveedor']), 'Proveedores', 'mif-chevron-right', $this->generateUrl('app_organizacion_new', ['tipo' => 'proveedor'])),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'entradas']), 'Listado de compras', 'mif-chevron-right', $this->generateUrl('app_movimiento_entrada_new')),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'devoluciones']), 'Devoluciones a proveedores', 'mif-chevron-right', $this->generateUrl('app_movimiento_devolucion_new')),
//                ]);
//        }

//
//        if($this->isGranted('ROLE_GESTOR')) {
//            $enlaces[] = new ModuloEnlace('', 'Inventario', 'mif-dashboard', null, [],
//                [
//                    new ModuloEnlace($this->generateUrl('app_producto_index'), 'Productos', 'mif-chevron-right', $this->generateUrl('app_producto_new')),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'ajustes']), 'Ajustes de inventario', 'mif-chevron-right', $this->generateUrl('app_movimiento_ajuste_inventario_new')),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_tipo_index', ['tipo' => 'transferencias']), 'Transferencia entre almacenes', 'mif-chevron-right', $this->generateUrl('app_movimiento_transferencia_almacen_new')),
//                    new ModuloEnlace($this->generateUrl('app_almacen_index'), 'Almacenes', 'mif-chevron-right', $this->generateUrl('app_almacen_new')),
//                    new ModuloEnlace($this->generateUrl('app_almacen_producto_index'), 'Almacenes y productos', 'mif-chevron-right'),
//                ]);
//        }

//        $enlaces[] = new ModuloEnlace('', 'Informes', 'mif-open-book', null, [],
//            [
//                new ModuloEnlace($this->generateUrl('app_movimiento_index'), 'Todos los movimientos', 'mif-chevron-right'),
//                new ModuloEnlace('', 'Resumen de movimientos', 'mif-open-book', null, [], [
//                    new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ymd'), 'frecuencia' => 'dia']), 'Resumen diario', 'mif-chevron-right', null, [], []),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Ym'), 'frecuencia' => 'mes']), 'Resumen mensual', 'mif-chevron-right', null, [], []),
//                    new ModuloEnlace($this->generateUrl('app_movimiento_resumen', ['fecha' => date('Y'), 'frecuencia' => 'anno']), 'Resumen anual', 'mif-chevron-right', null, [], []),
//                ])
//            ]);

        if($this->rolInRolHierarchy('ROLE_GESTOR')){
            $enlaces[] = new ModuloEnlace('', 'Configuración', 'mif-cogs', null, [],
                [
                    new ModuloEnlace($this->generateUrl('app_organizacion_current_show'), 'Perfil de la empresa', 'mif-chevron-right'),
                    new ModuloEnlace($this->generateUrl('app_usuario_index'), 'Usuarios', 'mif-chevron-right', $this->generateUrl('app_usuario_new')),
                    new ModuloEnlace($this->generateUrl('app_rol_index'), 'Roles', 'mif-chevron-right'),
                    new ModuloEnlace($this->generateUrl('app_database_backup_restore'), 'Salvar / Restaurar base de datos', 'mif-chevron-right', null, ['ROLE_ADMINISTRADOR_SISTEMA']),
                ]);
        }
        if($this->rolInRolHierarchy('ROLE_ADMINISTRADOR_SISTEMA')) {
            $enlaces[] = new ModuloEnlace('', 'Sistema', 'mif-cogs', null, [],
                [
                    new ModuloEnlace($this->generateUrl('app_sistema_registros_eventos'), 'Registro de eventos', 'mif-chevron-right', null, []),
                ]);
        }
        $enlaces[] = new ModuloEnlace($this->generateUrl('app_usuario_change_password', ['id' => $this->getUser()->getId()]), 'Cambiar mi contraseña', 'mif-key', null, [], []);
        $enlaces[] = new ModuloEnlace($this->generateUrl('app_license'), 'Licencia', 'mif-description', null, [], []);

        $sidebar[] = [
            'titulo' => 'Sistema',
            'enlaces' => $enlaces
        ];


//        $enlaces[] = new ModuloEnlace($this->generateUrl('app_almacen_index'), 'Almacenes');
//        $enlaces[] = new ModuloEnlace($this->generateUrl('app_producto_index'), 'Productos');
//        $enlaces[] = new ModuloEnlace($this->generateUrl('app_almacen_producto_index'), 'Almacenes y productos');
//        $enlaces[] = new ModuloEnlace($this->generateUrl('app_movimiento_index'), 'Movimientos');
//        $enlaces[] = new ModuloEnlace($this->generateUrl('app_cliente_proveedor_index'), 'Clientes / Proveedores');
////        $enlaces[] = new ModuloEnlace($this->generateUrl('app_usuario_index'), 'Usuarios');
////        $enlaces[] = new ModuloEnlace($this->generateUrl('app_rol_index'), 'Roles');

        dump($sidebar);
        return [
            'sidebar_menu' => $sidebar
        ];
    }

    private function getRandomColor() {
        $letters = explode(' ','0 1 2 3 4 5 6 7 8 9 A B C D E F');
        $color = '#';
        for ($i = 0; $i < 4; $i++ ) {
            $color .= $letters[rand(0,15)];
        }
        $color .= rand(0,1) === 0 ? 'FF' : 'CC';
        return [
            'backgroundColor' => $color . "CC",
            'borderColor' => $color
        ];
    }

    private function movimientosPorTipoChartData(MovimientoRepository $movimientoRepository, string $frecuencia = 'anno'){
        $data = [];
        $movimientosTipoCantidad = $movimientoRepository->findMovimientosPorTipo($frecuencia);

        switch ($frecuencia){
            case 'dia' : { break;}
            case 'mes' : { break;}
            case 'anno' : {
                $data['labels'] = [];
                $data['datasets'][0]['label'] = 'Movimientos por tipos. Año actual';
                $data['datasets'][0]['data'] = [];
                $data['datasets'][0]['borderColor'] = [];
                $data['datasets'][0]['backgroundColor'] = [];
                foreach($movimientosTipoCantidad as $movimiento){
                    $color = $this->getRandomColor();
                    $data['labels'][] = $movimiento['tipo'];
                    $data['datasets'][0]['data'][] = $movimiento['cantidad'];
                    $data['datasets'][0]['borderColor'][] = $color['borderColor'];
                    $data['datasets'][0]['backgroundColor'][] = $color['backgroundColor'];
                }

                break;}
        }
        return $data;
    }
}
