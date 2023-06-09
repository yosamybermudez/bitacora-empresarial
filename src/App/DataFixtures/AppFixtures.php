<?php

namespace App\DataFixtures;


use App\Entity\SistemaModulo;
use App\Entity\SistemaRol;
use AppSistema\Entity\Usuario;
use App\Interfaces\ModuloEnlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    private function numeroConCeros(int $number){
        return str_repeat('0', 4 - strlen((string) $number)) . $number;
    }

    public function load(ObjectManager $manager)
    {
        $usuario = $manager->getRepository(Usuario::class)->findOneByUsername('admin_dev');
        if(!$usuario) {
            //Usuario Admin
            $usuario = new Usuario();
            $usuario->setUsername('admin_dev');
            $usuario->setRoles(array('ROLE_ADMINISTRADOR_SISTEMA'));
            $usuario->setNombres('Administrador del sistema');
            $hashedPassword = $this->passwordHasher->hashPassword(
                $usuario,
                '*D3v3l0pm3nt*'
            );
            $usuario->setPassword($hashedPassword);
            $manager->getRepository(Usuario::class)->add($usuario, $usuario, true);
        }

        //Módulos
        //// Recursos humanos
        $identificador = 'mod_rec_humanos';
        $modulo = $manager->getRepository(SistemaModulo::class)->findOneByIdentificador($identificador);
        if(!$modulo) {
            $modulo = new SistemaModulo();
            $modulo
                ->setNombre('Recursos humanos')
                ->setIdentificador($identificador)
                ->setColorIdentificativo('cyan')
                ->setIconoMetroui('users');
            $manager->getRepository(SistemaModulo::class)->add($modulo, true);
        }
        //// Inventario
        $identificador = 'mod_inventario';
        $modulo = $manager->getRepository(SistemaModulo::class)->findOneByIdentificador($identificador);
        if(!$modulo) {
            $modulo = new SistemaModulo();
            $modulo
                ->setNombre('Inventario')
                ->setIdentificador($identificador)
                ->setColorIdentificativo('lightBlue')
                ->setIconoMetroui('dashboard');
            $manager->getRepository(SistemaModulo::class)->add($modulo, true);
        }
        //// Ventas
        $identificador = 'mod_ventas';
        $modulo = $manager->getRepository(SistemaModulo::class)->findOneByIdentificador($identificador);
        if(!$modulo) {
            $modulo = new SistemaModulo();
            $modulo
                ->setNombre('Ventas')
                ->setIdentificador($identificador)
                ->setColorIdentificativo('green')
                ->setIconoMetroui('cart');
            $manager->getRepository(SistemaModulo::class)->add($modulo, true);
        }
        //// Compras
        $identificador = 'mod_compras';
        $modulo = $manager->getRepository(SistemaModulo::class)->findOneByIdentificador($identificador);
        if(!$modulo) {
            $modulo = new SistemaModulo();
            $modulo
                ->setNombre('Compras')
                ->setIdentificador($identificador)
                ->setColorIdentificativo('teal')
                ->setIconoMetroui('shopping-basket');
            $manager->getRepository(SistemaModulo::class)->add($modulo, true);
        }
        //// Jurídica
        $identificador = 'mod_juridica';
        $modulo = $manager->getRepository(SistemaModulo::class)->findOneByIdentificador($identificador);
        if(!$modulo) {
            $modulo = new SistemaModulo();
            $modulo
                ->setNombre('Jurídica')
                ->setIdentificador($identificador)
                ->setColorIdentificativo('amber')
                ->setIconoMetroui('assignment');
            $manager->getRepository(SistemaModulo::class)->add($modulo, true);
        }
        //Roles
        $roles_array = array(
            'ADMINISTRADOR_SISTEMA' => 'Administrador del sistema',
            'ADMINISTRADOR_NEGOCIO' => 'Administrador del negocio',
            'GESTOR' => 'Usuario Gestor',
            'EDITOR' => 'Usuario Editor',
            'ESTANDAR' => 'Usuario Estándar'
        );
        foreach ($roles_array as $identificador => $nombre){
            $rol = $manager->getRepository(SistemaRol::class)->findOneByIdentificador($identificador);
            if(!$rol) {
                $rol = new SistemaRol();
                $rol->setNombre($nombre);
                $rol->setIdentificador("ROLE_" . $identificador);
                $manager->getRepository(SistemaRol::class)->add($rol, true);
            }
        }
/*
//        //Datos de la organizacion
//        $organizacion = new OrganizacionEmpresa();
//        $organizacion->setNombre('SaBer Photography');
//        $organizacion->setEsMiOrganizacion(true);
//        $manager->getRepository(OrganizacionEmpresa::class)->add($organizacion, $usuario, true);

//        //InventarioAlmacen
//        $almacen_1 = new InventarioAlmacen();
//        $almacen_1->setCodigo('ALM0001');
//        $almacen_1->setNombre('Insumos');
//        $manager->getRepository(InventarioAlmacen::class)->add($almacen_1, $usuario, true);
//
//        $almacen_2 = new InventarioAlmacen();
//        $almacen_2->setCodigo('ALM0002');
//        $almacen_2->setNombre('Piezas de repuesto');
//        $manager->getRepository(InventarioAlmacen::class)->add($almacen_2, $usuario, true);
//
//        $almacen_3 = new InventarioAlmacen();
//        $almacen_3->setCodigo('ALM0003');
//        $almacen_3->setNombre('Producción terminada');
//        $manager->getRepository(InventarioAlmacen::class)->add($almacen_3, $usuario, true);
//
//        //Cliente - Proveedor
//
//        $clienteProveedor_1 = new OrganizacionEmpresa();
//        $clienteProveedor_1->setNombre('Cliente 1');
//        $clienteProveedor_1->setEsCliente(true);
//        $manager->getRepository(OrganizacionEmpresa::class)->add($clienteProveedor_1, $usuario, true);
//
//        $clienteProveedor_2 = new OrganizacionEmpresa();
//        $clienteProveedor_2->setNombre('Proveedor 1');
//        $clienteProveedor_2->setEsProveedor(true);
//        $manager->getRepository(OrganizacionEmpresa::class)->add($clienteProveedor_2, $usuario, true);
//
//        $clienteProveedor_3 = new OrganizacionEmpresa();
//        $clienteProveedor_3->setNombre('Cliente 2');
//        $clienteProveedor_3->setEsCliente(true);
//        $manager->getRepository(OrganizacionEmpresa::class)->add($clienteProveedor_3, $usuario, true);
//
//        $clienteProveedor_4 = new OrganizacionEmpresa();
//        $clienteProveedor_4->setNombre('Proveedor 2');
//        $clienteProveedor_4->setEsProveedor(true);
//        $manager->getRepository(OrganizacionEmpresa::class)->add($clienteProveedor_4, $usuario, true);


//        //InventarioProducto
//
//        $filename = 'C:\Users\DD\Desktop\productos import.xlsx';
//
//
//        $testAgainstFormats = [
//            IOFactory::READER_XLS,
//            IOFactory::READER_XLSX,
//            IOFactory::READER_ODS,
//            IOFactory::READER_CSV
//        ];
//
//        $spreadsheet = IOFactory::load($filename, 0, $testAgainstFormats);
//        $sheetData = $spreadsheet->getActiveSheet()->toArray();
//        if (!empty($sheetData)) {
//            //Generar el codigo del movimiento. Ejemplo: ENT202301010001
//            $codigo = 'ENT' . '20210606' . $this->numeroConCeros(count($this->entityManager->getRepository(MovimientoEntrada::class)->findAll()) + 1) ;
//
//            //
//            $movimiento = new InventarioMovimiento();
//            $movimiento->setEstado('Confirmado');
//            $movimiento->setEmpresa($clienteProveedor_2);
//            $descripcion = sprintf('Entrada inicial para "%s"', $almacen_1->getNombre());
//            $movimiento->setDescripcion($descripcion);
//            $movimiento->setFecha(new \DateTime('2021-06-06'));
//            $movimiento->setCodigo($codigo);
//
//            $this->entityManager->getRepository(InventarioMovimiento::class)->add($movimiento, $usuario,true);
//
//            $movimientoEstado = new MovimientoEstado($movimiento, 'Confirmado');
//            $this->entityManager->getRepository(MovimientoEstado::class)->add($movimientoEstado, $usuario, true);
//
//            $importeTotalVigenteCup = $importeTotalVigenteMlc = 0;
//
//            for ($i = 1; $i < count($sheetData); $i++) { //Cada fila del excel
//                $producto = new InventarioProducto();
//                $producto->setUnidadMedida($sheetData[$i][1] ?: 'N/E');
//                $producto->setNombre($sheetData[$i][2] ?: 'InventarioProducto ' . $i);
//                $producto->setPrecioVentaCup($sheetData[$i][3] ?: 0);
//                $producto->setPrecioCompraCup(number_format($sheetData[$i][3] / 1.5 ?: 0, 2, '.', ''));
//                $producto->setPrecioVentaMlc(0);
//                $producto->setPrecioCompraMlc(0);
//                $manager->getRepository(InventarioProducto::class)->add($producto, $usuario, true);
//
//                $productoMovimiento = new InventarioProductoMovimiento();
//                $productoMovimiento->setPrecioCupVigente(0);
//                $productoMovimiento->setPrecioMlcVigente(0);
//                $productoMovimiento->setProducto($producto);
//                $productoMovimiento->setCantidad($sheetData[$i][0] ?: 0);
//                $productoMovimiento->setMovimiento($movimiento);
//                $manager->getRepository(InventarioProductoMovimiento::class)->add($productoMovimiento, $usuario, true);
//
//                $almacenProducto = new InventarioAlmacenProducto();
//                $almacenProducto->setProducto($producto);
//                $almacenProducto->setAlmacen($almacen_1);
//                $almacenProducto->setSaldoContable($sheetData[$i][0] ?: 0);
//                $almacenProducto->setSaldoDisponible($sheetData[$i][0] ?: 0);
//                $manager->getRepository(InventarioAlmacenProducto::class)->add($almacenProducto, $usuario, true);
//
//                $importeTotalVigenteCup += $productoMovimiento->getCantidad() * $productoMovimiento->getProducto()->getPrecioCompraCup();
//                $importeTotalVigenteMlc += $productoMovimiento->getCantidad() * $productoMovimiento->getProducto()->getPrecioCompraMlc();
//
//            }
//
//            $this->entityManager->getRepository(InventarioMovimiento::class)->add($movimiento, $usuario,true);
//
//            // Registrar el movimiento creado, como una Entrada a almacen
//            $movimientoEntrada = new MovimientoEntrada();
//            $movimientoEntrada->setAlmacen($almacen_1);
//            $movimientoEntrada->setMovimiento($movimiento);
//
//            $this->entityManager->getRepository(MovimientoEntrada::class)->add($movimientoEntrada, true);
//
//
//        }
//
//        for($i = 1; $i <= 50; $i++){
//            $producto = new InventarioProducto();
//            $producto->setNombre('InventarioProducto ' . $i);
//            $producto->setUnidadMedida('U');
//            $precio = rand(80,100);
//            $producto->setPrecioCompraCup($precio); // 100
//            $producto->setPrecioVentaCup($precio * 1.50); //50
//            $producto->setPrecioCompraMlc($precio / 80);
//            $producto->setPrecioVentaMlc($precio / 80 * 1.5);
//            $manager->getRepository(InventarioProducto::class)->add($producto, $usuario, true);
//        }




*/

    }
}
