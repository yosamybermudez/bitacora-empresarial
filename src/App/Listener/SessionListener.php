<?php

namespace App\Listener;

use App\Controller\AppController;
use App\Entity\Evento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class SessionListener
{
    private $doctrine;
    private $router;
    private $token;
    private $roleHierarchy;
    private $appController;

    public function __construct(EntityManagerInterface $em, Router $router, TokenStorage $tokenStorage, RoleHierarchyInterface $roleHierarchy, AppController $appController)
    {
        $this->doctrine = $em;
        $this->router = $router;
        $this->token = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;
        $this->appController = $appController;
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        try{
            $this->appController->eventoInicioSesion();
        }
        catch (\Exception $e){
            $this->appController->eventoExcepcion($e->getMessage());
        }

    }
}
