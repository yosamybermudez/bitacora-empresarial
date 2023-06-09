<?php

namespace App\Controller;

use App\Twig\AppExtension;
use AppSistema\Repository\VariableConfiguracionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Toastr\Prime\ToastrFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AppController extends AbstractController
{
    protected $toastr;
    protected $filter;
    protected $entityManager;
    protected $router;

    public function __construct(ToastrFactory $toastr, AppExtension $filter, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->toastr = $toastr;
        $this->filter = $filter;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    //Toastr
    public function notificacionRegistroSatisfactorio(): void
    {
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->success('Elemento registrado satisfactoriamente')
            ->timeOut(2000)
            ->progressBar()
            ->flash();
    }

    public function notificacionRegistroError(): void
    {
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->error('Ha ocurrido un error al registrar el elemento')
            ->timeOut(2000)
            ->progressBar()
            ->flash();
    }

    public function notificacionModificacionSatisfactoria(): void
    {
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->success('Elemento modificado satisfactoriamente')
            ->timeOut(2000)
            ->progressBar()
            ->flash();
    }

    public function notificacionModificacionError(): void
    {
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->error('Ha ocurrido un error al modificar el elemento')
            ->timeOut(2000)
            ->progressBar()
            ->flash();
    }

    public function notificacionEliminacionSatisfactoria(): void
    {
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->success('Elemento eliminado satisfactoriamente')
            ->timeOut(2000)
            ->progressBar()
            ->flash();
    }

    public function notificacionEliminacionError(): void
    {
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->error('Ha ocurrido un error al eliminar el elemento')
            ->timeOut(2000)
            ->progressBar()
            ->flash();
    }

    public function notificacionSatisfactoria(string $message): void
    {
        $timeout = strlen($message) > 50 ? 5000 : 2000;
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->success($message)
            ->timeOut($timeout)
            ->progressBar()
            ->flash();
    }

    public function notificacionAdvertencia(string $message): void
    {
        $timeout = strlen($message) > 50 ? 5000 : 2000;
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->warning($message)
            ->timeOut($timeout)
            ->progressBar()
            ->flash();
    }

    public function notificacionError(string $message): void
    {
        $timeout = strlen($message) > 50 ? 5000 : 2000;
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->error($message)
            ->timeOut($timeout)
            ->progressBar()
            ->flash();
    }

    public function notificacionErrorInesperado(): void
    {
        $timeout = 2000;
        $this->toastr->title('Notificación')
            ->positionClass('toast-bottom-right')
            ->error('Ha ocurrido un error inesperado')
            ->timeOut($timeout)
            ->progressBar()
            ->flash();
    }

    public function notificacionExcepcion(string $message): void
    {
        if($this->isGranted('ROLE_ADMINISTRADOR_SISTEMA')){
            $timeout = strlen($message) > 50 ? 5000 : 2000;
            $this->toastr->title('Para el desarrollador')
                ->positionClass('toast-bottom-right')
                ->warning($message)
                ->timeOut($timeout)
                ->progressBar()
                ->flash();
        }
    }

    //Evento
    public function eventoRegistroSatisfactorio(): void
    {
        $this->registrarEventoSistema('Información', $this->obtenerFuncionActivada(), $this->getUser(), 'Elemento registrado satisfactoriamente', 1);
    }

    public function eventoRegistroError(): void
    {
        $this->registrarEventoSistema('Error', $this->obtenerFuncionActivada(), $this->getUser(), 'Ha ocurrido un error al registrar el elemento', 1);

    }

    public function eventoModificacionSatisfactoria($oldEntidad): void
    {
        $this->registrarEventoSistema('Información', $this->obtenerFuncionActivada(), $this->getUser(), 'Elemento modificado satisfactoriamente', 2, 'old_object_json::' . $oldEntidad->toJson());
    }

    public function eventoModificacionError(): void
    {
        $this->registrarEventoSistema('Error', $this->obtenerFuncionActivada(), $this->getUser(), 'Ha ocurrido un error al modificar el elemento', 2);
    }

    public function eventoEliminacionSatisfactoria($informacion_extra): void
    {
        $this->registrarEventoSistema('Información', $this->obtenerFuncionActivada(), $this->getUser(), 'Elemento eliminado satisfactoriamente', 3);
    }

    public function eventoEliminacionError(): void
    {
        $this->registrarEventoSistema('Error', $this->obtenerFuncionActivada(), $this->getUser(), 'Ha ocurrido un error al eliminar el elemento', 3);
    }

    public function eventoError(string $mensaje = null): void
    {
        $this->registrarEventoSistema('Error', $this->obtenerFuncionActivada(), $this->getUser(), $mensaje ?: 'Ha ocurrido un error inesperado', 7);
    }

    public function eventoExcepcion(string $exceptionMessage): void
    {
        $this->registrarEventoSistema('Excepción', $this->obtenerFuncionActivada(), $this->getUser(), $exceptionMessage, 6);
    }

    public function eventoMovimientoConfirmar(Movimiento $movimiento): void
    {
        $this->registrarEventoSistema('Información', $this->obtenerFuncionActivada(), $this->getUser(), sprintf('Movimiento %s confirmado satisfactoriamente', $movimiento->getCodigo()), 2);
    }

    public function eventoInicioSesion(): void
    {
        $this->registrarEventoSistema('Información', $this->obtenerFuncionActivada(), $this->getUser(), 'Inicio de sesión del usuario: ' . $this->getUser()->getUserIdentifier(),5);
    }

    public function eventoCierreSesion(): void
    {
        $this->registrarEventoSistema('Información', $this->obtenerFuncionActivada(), $this->getUser(), 'Cierre de sesión del usuario: ' . $this->getUser()->getUserIdentifier(),6);
    }



    public function registrarEventoSistema(string $nivel, string $origen, Usuario $registradoPor, string $descripcion, int $tipo = 0, string $informacionExtra = null){
        $evento = new Evento($nivel, $origen, $registradoPor, $descripcion, $tipo, $informacionExtra);
        $this->entityManager->getRepository(Evento::class)->add($evento, true);
    }

    public function obtenerEnlaceActual(){
        $context = $this->router->getContext();
        $path = sprintf('%s %s://%s%s',
            $context->getMethod(),
            $context->getScheme(),
            $context->getHost() . ($context->getScheme() === 'http' && $context->getHttpPort() !== 80 ?  ':' . $context->getHttpPort() : ''),
            $context->getPathInfo()
        );
        return $path;
    }

    public function obtenerFuncionActivada()
    {
        $trace = debug_backtrace();
        $previousCall = $trace[2]; // 0 is this call, 1 is call in previous function, 2 is caller of that function

        return $previousCall['class'] . "::" . $previousCall['function'];
    }

    public function _number_format($number){
        return number_format($number, 2, '.', '');
    }

    public function redirectToReferer(){
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function numeroConCeros(int $number){
        return str_repeat('0', 4 - strlen((string) $number)) . $number;
    }

    public function numeroAgregarCero(int $number){
        return $number < 10 ? '0' . $number : $number;
    }

    public function rolInUserRoles(string $rol){
        dump($this->getUser());
        if(!$this->getUser()){
            return false;
        }
        return in_array($rol, $this->getUser()->getRoles());
    }

    public function rolInRolHierarchy(string $rol){
        return $this->filter->hasAccessFilter([$rol]);
    }

    public function getConfiguration(string $variable){
        $var = $this->config->findOneBy(['nombre' => $variable]);
        return $var;
    }

    public function getConfigurationValue(string $variable){
        $var = $this->config->findOneBy(['nombre' => $variable]);
        return $var ?  $var->getValor() : '';
    }

    public function checkLicense(string $license_encoded){
        $license_decoded = base64_decode($license_encoded);
        $parts = explode("___", $license_decoded);

        try{
            $license = [
                'starts' => new \DateTime($parts[0]),
                'type' => substr($parts[1],0,1) ?: null,
                'period' => substr($parts[1],1,1) ?: null,
                'limit' => substr($parts[1],2) ?: null,
                'organization' => $parts[2],
                'machine' => $parts[3],
            ];

            $errors = [];

            if(!isset($license['starts']) || !$license['starts']){
                $errors[] = 'starts';
            }
            if(!isset($license['type']) || !in_array($license['type'], ['T', 'P', 'S'])){
                $errors[] = 'type';
            }
            if(isset($license['type']) and in_array($license['type'], ['T', 'S'])){
                if(!isset($license['period']) || !in_array($license['period'], ['Y', 'M', 'D'])){
                    $errors[] = 'period';
                }
                if(!isset($license['limit']) || $license['limit'] <= 0){
                    $errors[] = 'limit';
                }
                if(in_array($license['period'], ['Y', 'M', 'D'])){
                    if($license['period'] === 'Y') { $type = 'year'; }
                    elseif($license['period'] === 'M') { $type = 'month'; }
                    else { $type = 'day'; }

                    $date = clone $license['starts'];

                    $license['end_date'] = date_modify($date, "+" . $license['limit'] . $type);
                    $diff = date_diff(new \DateTime(), $license['end_date']);
                    if($diff->days > 0 and $diff->invert === 1){
                        $license['status'] = 'overdue';
                    } else {
                        $license['status'] = 'valid';
                    }

                } else{
                    $errors[] = 'period_type';
                }
            }
            if(!isset($license['machine']) || $license['machine'] !== php_uname()){
                $errors[] = 'machine';
            }
            if(!isset($license['organization']) || $license['organization'] !== $this->getMyOrganization()->getNombre()){
                $errors[] = 'organization';
            }
            if(count($errors) !== 0){
                return [
                    'status' => 'invalid'
                ];
            }
        }
        catch (\Exception $e){

            return [
                'status' => 'invalid'
            ];
        }
        return $license;
    }

    public function getLicense(){
        $license_encoded = $this->getConfigurationValue('license_key');
        $license = $this->checkLicense($license_encoded);
        $license['request_code'] = $this->generateRequestCode();
        return $license;
    }

    public function setLicense(string $license_encoded){
        //Actualizar la licencia
        $license_key = $this->getConfiguration('license_key');
        $license_key->setValor($license_encoded);
        $this->config->add($license_key, true);
        return $this->getLicense();
    }

    private function getMyOrganization(): ?Organizacion
    {
        return $this->entityManager->getRepository(Organizacion::class)->findOneBy(['esMiOrganizacion' => true]);
    }

    public function generateStarterLicense(){
        $machine = php_uname();
        $organization = $this->getMyOrganization()->getNombre();
        $type = 'S';
        $limit = 30;
        $period = 'D'; // M , Y
        $license = implode('___', [date('YmdHis'), $type . $period . $limit, $organization, $machine]);

        return base64_encode($license);
    }

    public function generateRequestCode(){
        $machine = php_uname();
        $organization = $this->getMyOrganization()->getNombre();
        $license = implode('___', [$organization, $machine]);

        return base64_encode($license);
    }

    public function customSerializer($object, array $ignored_attributes = [])
    {
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        return $serializer->serialize($object, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => $ignored_attributes,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true
        ]);
    }
}
