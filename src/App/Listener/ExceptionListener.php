<?php


namespace App\Listener;


use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Router;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class ExceptionListener
{

    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $url = $this->router->generate('_error_404');
        $urlActual = $this->router->getContext()->getScheme().'://'
            . $this->router->getContext()->getHost()
            . (($this->router->getContext()->getHttpPort() !== 80) ? ':' . $this->router->getContext()->getHttpPort() : '')
            . $this->router->getContext()->getBaseUrl()
            . $this->router->getContext()->getPathInfo();
        $response = new RedirectResponse($url);
        $response->headers->clearCookie('code');
        $response->headers->clearCookie('mensaje');
        $response->headers->clearCookie('url_actual');
        $response->headers->clearCookie('file');
        $response->headers->clearCookie('line');

        switch (get_class($exception)){
            case 'Doctrine\DBAL\Exception\ConnectionException': {
                if($exception->getCode() === 2002){
                    echo "<h1>No existe conexión con la base de datos.</h1>";
                    echo "Revise los parámetros de configuración y asegúrese que el servidor de base de datos está activo. Luego actualice esta página.";
                    exit();
                } elseif($exception->getCode() === 1049){
                    $url = $this->router->generate('app_security_login');
                    $response = new RedirectResponse($url);
                    return $response;
                }
            }
        }


        if ($exception instanceof AccessDeniedHttpException){
            $response->headers->setCookie(new Cookie('mensaje', 'El usuario no tiene acceso al recurso solicitado.'));
            $response->headers->setCookie(new Cookie('code', $exception->getMessage()));
        } else if ($exception instanceof NotFoundHttpException) {
            $response->headers->setCookie(new Cookie('mensaje', 'No pudimos encontrar la página que estabas buscando.'));
            $response->headers->setCookie(new Cookie('code', $exception->getMessage()));
        } else if ($exception instanceof RouteNotFoundException){
            $response->headers->setCookie(new Cookie('mensaje', 'RouteNotFoundException'));
            $response->headers->setCookie(new Cookie('code', $exception->getMessage()));
        } else if ($exception instanceof ClassNotFoundException) {
            $response->headers->setCookie(new Cookie('mensaje', $exception->getMessage()));
            $response->headers->setCookie(new Cookie('code', $exception));
        } else{
            $response->headers->setCookie(new Cookie('mensaje', "Algo ha salido mal :("));
            if (strpos($exception->getMessage(),'DETAIL' )){
                $mensaje=explode('DETAIL: ',$exception->getMessage())[1];
            }else{
                $mensaje=$exception->getMessage();

            }
            $response->headers->setCookie(new Cookie('code',$mensaje));
        }

        if($_ENV["APP_ENV"] === 'dev'){
            $response->headers->setCookie(new Cookie('file', $exception->getFile()));
            $response->headers->setCookie(new Cookie('line', $exception->getLine()));
        }

        $response->headers->setCookie(new Cookie('url_actual',$urlActual));

        $event->setResponse($response);

    }

}