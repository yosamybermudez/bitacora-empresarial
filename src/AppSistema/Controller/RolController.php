<?php

namespace AppSistema\Controller;

use App\Controller\AppController;
use AppSistema\Entity\Rol;
use AppSistema\Form\RolType;
use AppSistema\Repository\RolRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rol")
 */
class RolController extends AppController
{
    /**
     * @Route("/", name="app_rol_index", methods={"GET"})
     */
    public function index(RolRepository $rolRepository): Response
    {
        return $this->render('rol/index.html.twig', [
            'rols' => $rolRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_rol_show", methods={"GET"})
     */
    public function show(Rol $rol): Response
    {
        return $this->render('rol/show.html.twig', [
            'rol' => $rol,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_rol_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Rol $rol, RolRepository $rolRepository): Response
    {
        $form = $this->createForm(RolType::class, $rol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->isSubmitted() && $form->isValid()) {
                try{
                    $rolRepository->add($rol, true);
                    $this->notificacionSatisfactoria('Datos del rol modificados satisfactoriamente');
                    /** EVENTO **/
                    $this->registrarEventoSistema('Información', get_class($this), $this->getUser(), 'El rol ' . $rol->getIdentificador() . 'ha sido modificado', 2);
                    /*********/
                } catch (UniqueConstraintViolationException $e){
                    $this->notificacionError('Ya existe un rol con el nombre ' . $rol->getNombre());
                    $this->notificacionExcepcion($e->getMessage());
                    /** EVENTO **/
                    $this->registrarEventoSistema('Error', get_class($this), $this->getUser(), $e->getMessage(), 2);
                    /*********/
                } catch (\Exception $ex){
                    $this->notificacionErrorInesperado();
                    $this->notificacionExcepcion($ex->getMessage());
                    /** EVENTO **/
                    $this->registrarEventoSistema('Error', get_class($this), $this->getUser(), $ex->getMessage(), 4);
                    /*********/
                }

                return $this->redirectToRoute('app_rol_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('rol/new.html.twig', [
            'rol' => $rol,
            'form' => $form,
        ]);
    }
}
