<?php

namespace AppSistema\Controller;

use AppSistema\Entity\SistemaModulo;
use AppSistema\Repository\SistemaModuloRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModuloController extends AbstractController
{
    /**
     * @Route("/modulo/{id}", name="app_sistema_modulo_show")
     */
    public function show(SistemaModuloRepository $sistemaModuloRepository, string $id): Response
    {
        $modulo = $sistemaModuloRepository->findOneByIdentificador($id);
//        dd($modulo);
        return $this->redirectToRoute(
            sprintf('app_mod_%s_dashboard', $modulo->getIdentificador())
        );
    }

    /**
     * @Route("/install/module/activation", name="app_sistema_modulo_activation")
     */
    public function activate(EntityManagerInterface $entityManager, Request $request): Response
    {
        $modulos = $entityManager->getRepository(SistemaModulo::class)->findAll();
        if($request->getMethod() === 'POST'){
            $data = $request->request->all();
            foreach ($data as $module_id => $value){
                $module = $entityManager->getRepository(SistemaModulo::class)->findOneByIdentificador($module_id);
                $module->setActivado(true);
                $entityManager->getRepository(SistemaModulo::class)->add($module, true);
            }
            $this->notificacionSatisfactoria('Módulos activados satisfactoriamente. Inicie sesión');
            return $this->redirectToRoute('app_security_login');
        }

        return $this->renderForm('app/security/module_activation.html.twig', [
            'modulos' => $modulos,
        ]);
    }


}
