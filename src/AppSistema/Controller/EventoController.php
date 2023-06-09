<?php

namespace AppSistema\Controller;

use AppSistema\Repository\EventoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventoController extends AbstractController
{
    /**
     * @Route("/sistema/registros/eventos", name="app_sistema_registros_eventos")
     */
    public function index(EventoRepository $eventoRepository): Response
    {
        return $this->render('sistema_evento/index.html.twig', [
            'eventos' => $eventoRepository->findBy([], ['registradoEn' => 'DESC']),
        ]);
    }
}
