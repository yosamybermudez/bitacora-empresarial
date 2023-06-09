<?php

namespace ModRecursosHumanos\Controller;

use App\Controller\AppController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rec_humanos")
 */
class ModRecursosHumanosController extends AppController
{
    /**
     * @Route("/", name="app_mod_recursos_humanos_dashboard")
     */
    public function dashboard(): Response
    {
        return $this->render('mod_recursos_humanos/index.html.twig', [
            'controller_name' => 'ModRecursosHumanosController',
        ]);
    }
}
