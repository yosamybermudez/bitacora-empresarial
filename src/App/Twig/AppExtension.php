<?php

namespace App\Twig;

use App\Entity\Rol;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use RRule\RRule;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    private $security;
    private $entityManager;
    private $roleHierarchy;
    private $accessMap;

    public function __construct(Security $security, EntityManagerInterface $entityManager, RoleHierarchyInterface $roleHierarchy)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('convertir_a_romano', [$this, 'convertirARomanoFilter']),
            new TwigFilter('pascal_to_snake_case',array($this, 'pascalToSnakeCase')),
            new TwigFilter('tipo_movimiento',array($this, 'tipoMovimientoFilter')),
            new TwigFilter('rol_nombre',array($this, 'rolNombreFilter')),
            new TwigFilter('get_action',array($this, 'getAction')),
            new TwigFilter('tipo_movimiento_codigo_es',array($this, 'tipoMovimientoCodigoESFilter')),
            new TwigFilter('tipo_movimiento_identificador_singular_es',array($this, 'tipoMovimientoIdentificadorSingularESFilter')),
            new TwigFilter('tipo_movimiento_identificador_plural_es',array($this, 'tipoMovimientoIdentificadorPluralESFilter')),
            new TwigFilter('minusculas_sin_espacio',array($this, 'minusculasSinEspacioFilter')),
            new TwigFilter('quitar_underscore',array($this, 'quitarUnderscoreFilter')),
            new TwigFilter('plural',array($this, 'pluralFilter')),
            new TwigFilter('edad', array($this, 'edadFilter')),
            new TwigFilter('fecha_nacimiento', array($this, 'fechaNacimientoFilter')),
            new TwigFilter('fecha_es', array($this, 'fechaEsFilter')),
            new TwigFilter('fecha_mes_es', array($this, 'fechaMesEsFilter')),
            new TwigFilter('array_sum_key_value', array($this, 'arraySumKeyValueFilter')),
            new TwigFilter('empty_value', array($this, 'emptyValueFilter')),
            new TwigFilter('empty_value_ne', array($this, 'emptyValueNEFilter')),
            new TwigFilter('mes_es', array($this, 'mesFilter')),
            new TwigFilter('resumen_mes_es', array($this, 'resumenMesFilter')),
            new TwigFilter('letras', array($this, 'letrasFilter')),
            new TwigFilter('safe_encrypt', array($this, 'safeEncryptFilter')),
            new TwigFilter('safe_decrypt', array($this, 'safeDecryptFilter')),
            new TwigFilter('obtener_anno', array($this, 'obtenerAnnoFilter')),
            new TwigFilter('has_access', [$this, 'hasAccessFilter']),
            new TwigFilter('remote_file_exists', [$this, 'remoteFileExistsFilter']),
            new TwigFilter('server_date', [$this, 'serverDateFilter']),
            new TwigFilter('get_class', [$this, 'getClassFilter']),
        ];
    }

    public function convertirARomano(int $numero){
        $conversiones = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'DC' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];

        $resultado = '';
        foreach ($conversiones as $simbolo => $valor){
            while ($numero >= $valor){
                $resultado .= $simbolo;
                $numero -= $valor;
            }
        }
        return $resultado;
    }

    public function pascalToSnakeCase($cadena){
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $cadena));
    }

    public function rolNombreFilter($rol_identificador){
        return $rol_identificador !== 'ROLE_ESPECIALISTA' ? $this->entityManager->getRepository(Rol::class)->findOneBy(['identificador' => $rol_identificador])->getNombre() : 'Especialista';
    }

    public function arraySumKeyValueFilter($array){
        $sum = array_sum($array);
        return $sum;
    }

    public function getAction($action){
        if(strpos($action, 'edit')){
            return 'Editar ';
        }

        if(strpos($action, 'new')){
            return 'Registrar ';
        }

        if(strpos($action, 'show')){
            return 'Datos del ';
        }

        if(strpos($action, 'change_password')){
            return 'Cambiar contraseña de ';
        }

        if(strpos($action, 'duplicate')){
            return 'Duplicar ';
        }

        return 'Gestionar ';

    }

    public function minusculasSinEspacioFilter($cadena){

        return strtolower(str_replace(' ', '_', $cadena));
    }

    public function quitarUnderscoreFilter($cadena){

        return strtolower(str_replace('_', ' ', $cadena));
    }

    public function hasAccessFilter(array $userRoles)
    {
        if(!$this->security->getUser()){
            return false;
        }
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($this->security->getUser()->getRoles());
        return count(array_unique(array_intersect($userRoles, $reachableRoles))) > 0;
    }

    public function remoteFileExistsFilter($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status === 200 ? true : false;
    }

    public function serverDateFilter(){
        $meses = array(1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre');
        $dias_semana = array(1 => 'Lunes', 2 => 'Martes',  3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo');
        $date = new \DateTime();
        $dia_semana = $dias_semana[$date->format('N')];
        $dia = sprintf("%s%s",$date->format('j'), $date->format('j') === '1' ? 'º' : '');
        $mes = strtolower($meses[$date->format('n')]);
        $anno = $date->format('Y');
        $result = sprintf('%s, %s de %s de %s ', $dia_semana, $dia, $mes, $anno);
        return $result;


    }

    public function getClassFilter($var)
    {
        return get_class($var);
    }

    public function tipoMovimientoFilter($codigo){
        return substr($codigo, 0, 3);
    }

    public function tipoMovimientoCodigoESFilter($codigo){
        switch (substr($codigo, 0, 3)){
            case "COM": { return 'Compra'; break;}
            case "VEN": { return 'Venta'; break;}
            case "RET": { return 'Retorno'; break;}
            case "DEV": { return 'Devolución'; break;}
            case "AJT": { return 'Ajuste de inventario'; break;}
            case "TRF": { return 'Transferencia entre almacenes'; break;}
            default: { return '';}
        }
    }

    public function tipoMovimientoIdentificadorSingularESFilter($identificador){
        switch ($identificador){
            case "entradaCompras": { return 'Compra'; break;}
            case "salidaVentas": { return 'Venta'; break;}
            case "entradaDevoluciones": { return 'Devolución de cliente'; break;}
            case "salidaDevoluciones": { return 'Devolución a proveedor'; break;}
            case "internoAjustesInventario": { return 'Ajuste de inventario'; break;}
            case "internoTransferenciasAlmacen": { return 'Transferencia entre almacenes'; break;}

            default: { return '';}
        }
    }

    public function tipoMovimientoIdentificadorPluralESFilter($identificador){
        switch ($identificador){
            case "entradaCompras": { return 'Compras'; break;}
            case "salidaVentas": { return 'Ventas'; break;}
            case "entradaDevoluciones": { return 'Devoluciones de clientes'; break;}
            case "salidaDevoluciones": { return 'Devoluciones a proveedores'; break;}
            case "internoAjustesInventario": { return 'Ajustes de inventario'; break;}
            case "internoTransferenciasAlmacen": { return 'Transferencias entre almacenes'; break;}

            default: { return '';}
        }
    }

    public function pluralFilter($texto)
    {
        $vowels = array('a','e','i','o','u');
        $ch = $texto[strlen($texto) - 1];
        if(in_array(strtolower($ch), $vowels)){
            return $texto . 's';
        } else {
            return $texto . 'es';
        }
    }

    public function fechaNacimientoFilter($carneIdentidad)
    {
        if (substr($carneIdentidad, 0, 1) == '0') {
            $anno = '20'.substr($carneIdentidad, 0, 2);
        } else {
            $anno = '19'.substr($carneIdentidad, 0, 2);
        }
        $mes = substr($carneIdentidad, 2, 2);
        $dia = substr($carneIdentidad, 4, 2);
        return $dia.' de '.$this->mesFilter($mes).' del '.$anno;
    }

    public function fechaEsFilter($fecha)
    {
        if (!is_a($fecha, \DateTime::class)) {
            $fecha = new \DateTime($fecha);
        }

        return date('j', $fecha->getTimestamp()).' de '.$this->mesFilter(date('n', $fecha->getTimestamp())).' de '.date('Y', $fecha->getTimestamp());
    }

    public function fechaMesEsFilter($fecha)
    {
        if (!is_a($fecha, \DateTime::class)) {
            $fecha = new \DateTime($fecha);
        }

        return $this->mesFilter(date('n', $fecha->getTimestamp()));
    }

    public function emptyValueFilter($var)
    {
        if (is_null($var) || $var =='' || $var == 0) {
            return '-';
        } else {
            return $var;
        }
    }

    public function emptyValueNEFilter($var)
    {
        if (is_null($var) || $var =='' || $var == 0) {
            return 'N/E';
        } else {
            return $var;
        }
    }

    public function safeEncryptFilter(string $message)
    {
        if (mb_strlen('aaa', '8bit') != SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \RangeException('Key is not the correct size (must be 32 bytes).');
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $cipher = base64_encode(
            $nonce.
            sodium_crypto_secretbox(
                $message,
                $nonce,
                $key
            )
        );
        sodium_memzero($message);
        sodium_memzero($key);
        return $cipher;
    }

    public function safeDecryptFilter(string $encrypted, string $key)
    {
        $decoded = base64_decode($encrypted);
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $key
        );
        if (!is_string($plain)) {
            throw new Exception('Invalid MAC');
        }
        sodium_memzero($ciphertext);
        sodium_memzero($key);
        return $plain;
    }

    public function mesFilter($mes)
    {
        $mes = intval($mes);
        switch ($mes) {
            case 1: return "enero";
            case 2: return "febrero";
            case 3: return "marzo";
            case 4: return "abril";
            case 5: return "mayo";
            case 6: return "junio";
            case 7: return "julio";
            case 8: return "agosto";
            case 9: return "septiembre";
            case 10: return "octubre";
            case 11: return "noviembre";
            case 12: return "diciembre";
        }
    }

    public function resumenMesFilter($mes)
    {
        switch ($mes) {
            case '1': return "Ene";
            case '2': return "Feb";
            case '3': return "Mar";
            case '4': return "Abr";
            case '5': return "May";
            case '6': return "Jun";
            case '7': return "Jul";
            case '8': return "Ago";
            case '9': return "Sept";
            case '10': return "Oct";
            case '11': return "Nov";
            case '12': return "Dic";
        }
    }

    public function edadFilter($carneIdentidad)
    {
        if (substr($carneIdentidad, 0, 1) == '0') {
            $anno = '20'.substr($carneIdentidad, 0, 2);
        } else {
            $anno = '19'.substr($carneIdentidad, 0, 2);
        }
        $mes = substr($carneIdentidad, 2, 2);
        $dia = substr($carneIdentidad, 4, 2);
        $fecha_nac = $anno.'-'.$mes.'-'.$dia;
        $hoy = new \DateTime();
        $fecha = new \DateTime($fecha_nac);
        $edad = $hoy->diff($fecha);
        return $edad->y;
        //$fecha_nac->setDate($anno::int,$mes::int,$dia::int);
    }

    public function letras($xcifra)
    {
        $xarray = array(0 => "Cero",
            1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
            "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
            "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
            100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
        );
//
        $xcifra = trim($xcifra);
        $xlength = strlen($xcifra);
        $xpos_punto = strpos($xcifra, ".");
        $xaux_int = $xcifra;
        $xdecimales = "00";
        if (!($xpos_punto === false)) {
            if ($xpos_punto == 0) {
                $xcifra = "0" . $xcifra;
                $xpos_punto = strpos($xcifra, ".");
            }
            $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
            $xdecimales = substr($xcifra . "00", $xpos_punto + 1, 2); // obtengo los valores decimales
        }

        $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
        $xcadena = "";
        for ($xz = 0; $xz < 3; $xz++) {
            $xaux = substr($XAUX, $xz * 6, 6);
            $xi = 0;
            $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
            $xexit = true; // bandera para controlar el ciclo del While
            while ($xexit) {
                if ($xi == $xlimite) { // si ya llegó al límite máximo de enteros
                    break; // termina el ciclo
                }

                $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
                $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
                for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
                    switch ($xy) {
                        case 1: // checa las centenas
                            if (substr($xaux, 0, 3) < 100) { // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas
                            } else {
                                $key = (int) substr($xaux, 0, 3);
                                if (true === array_key_exists($key, $xarray)) {  // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                                    $xseek = $xarray[$key];
                                    $xsub = $this->subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
                                    if (substr($xaux, 0, 3) == 100) {
                                        $xcadena = " " . $xcadena . " CIEN " . $xsub;
                                    } else {
                                        $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                    }
                                    $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                                } else { // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                                    $key = (int) substr($xaux, 0, 1) * 100;
                                    $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                    $xcadena = " " . $xcadena . " " . $xseek;
                                } // ENDIF ($xseek)
                            } // ENDIF (substr($xaux, 0, 3) < 100)
                            break;
                        case 2: // checa las decenas (con la misma lógica que las centenas)
                            if (substr($xaux, 1, 2) < 10) {
                            } else {
                                $key = (int) substr($xaux, 1, 2);
                                if (true === array_key_exists($key, $xarray)) {
                                    $xseek = $xarray[$key];
                                    $xsub = $this->subfijo($xaux);
                                    if (substr($xaux, 1, 2) == 20) {
                                        $xcadena = " " . $xcadena . " VEINTE " . $xsub;
                                    } else {
                                        $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                    }
                                    $xy = 3;
                                } else {
                                    $key = (int) substr($xaux, 1, 1) * 10;
                                    $xseek = $xarray[$key];
                                    if (20 == substr($xaux, 1, 1) * 10) {
                                        $xcadena = " " . $xcadena . " " . $xseek;
                                    } else {
                                        $xcadena = " " . $xcadena . " " . $xseek . " Y ";
                                    }
                                } // ENDIF ($xseek)
                            } // ENDIF (substr($xaux, 1, 2) < 10)
                            break;
                        case 3: // checa las unidades
                            if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada
                            } else {
                                $key = (int) substr($xaux, 2, 1);
                                $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
                                $xsub = $this->subfijo($xaux);
                                $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                            } // ENDIF (substr($xaux, 2, 1) < 1)
                            break;
                    } // END SWITCH
                } // END FOR
                $xi = $xi + 3;
            } // ENDDO

            if (substr(trim($xcadena), -5, 5) == "ILLON") { // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
                $xcadena.= " DE";
            }

            if (substr(trim($xcadena), -7, 7) == "ILLONES") { // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
                $xcadena.= " DE";
            }

            // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
            if (trim($xaux) != "") {
                switch ($xz) {
                    case 0:
                        if (trim(substr($XAUX, $xz * 6, 6)) == "1") {
                            $xcadena.= "UN BILLON ";
                        } else {
                            $xcadena.= " BILLONES ";
                        }
                        break;
                    case 1:
                        if (trim(substr($XAUX, $xz * 6, 6)) == "1") {
                            $xcadena.= "UN MILLON";
                        } else {
                            $xcadena.= " MILLONES";
                        }
                        break;
                    case 2:
                        if ($xcifra < 1) {
                            $xcadena = "CERO CON $xdecimales/100";
                        }
                        if ($xcifra >= 1 && $xcifra < 2) {
                            $xcadena = "UNO CON $xdecimales/100";
                        }
                        if ($xcifra >= 2) {
                            $xcadena.= " CON $xdecimales/100"; //
                        }
                        break;
                } // endswitch ($xz)
            } // ENDIF (trim($xaux) != "")
            // ------------------      en este caso, para México se usa esta leyenda     ----------------
            $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
            $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
            $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
            $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
            $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
            $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
            $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
        } // ENDFOR ($xz)
        return trim($xcadena);
    }

    // END FUNCTION

    private function subfijo($xx)
    { // esta función regresa un subfijo para la cifra
        $xx = trim($xx);
        $xstrlen = strlen($xx);
        if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3) {
            $xsub = "";
        }
        //
        if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6) {
            $xsub = "MIL";
        }
        //
        return $xsub;
    }

    public function obtenerAnnoFilter($fecha)
    { // esta función regresa un subfijo para la cifra
        $anno = date('Y', $fecha);

        return strval($anno);
    }
}
