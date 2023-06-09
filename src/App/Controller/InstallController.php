<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/app/install")
 */
class InstallController extends AppController
{

    /**
     * @Route("/", name="app_install", methods="GET")
     */
    public function install()
    {
        dd('install');
        return $this->render('_themes/metroui/base_error_404.html.twig');
    }
}
