<?php

namespace App\Controller;

use AppSistema\Repository\ModuloRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/app/render")
 */
class RenderController extends AppController
{
    /**
     * @Route("/activated_module_data/{id}", name="app_render_activated_module_data")
     */
    public function renderActivatedModuleData(ModuloRepository $sistemaModuloRepository, string $id): Response
    {
        $modulo = $sistemaModuloRepository->findOneByIdentificador($id);
        return new Response(
            '<span class="mif-' . $modulo->getIconoMetroui() . '"></span> Módulo <b>' . $modulo->getNombre() . '</b>'
        );
    }

    /**
     * @Route("/sidebar/{module_id}", name="app_render_sidebar", methods="GET")
     */
    public function renderSidebar(PrincipalController $principalController, SistemaModuloRepository $sistemaModuloRepository, string $module_id = null)
    {
        return $this->render('_themes/metroui/sidebar.html.twig', ['sidebar_menu' => $principalController->obtenerEnlaces($module_id), 'modulo' => $module_id ? $sistemaModuloRepository->findOneByIdentificador($module_id) : null]);
    }
}
