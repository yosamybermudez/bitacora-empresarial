<?php

namespace AppSistema\Controller;

use App\Controller\AppController;
use AppSistema\Entity\Rol;
use AppSistema\Entity\Usuario;
use AppSistema\Form\UsuarioType;
use AppSistema\Repository\UsuarioRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/usuario")
 */
class UsuarioController extends AppController
{
    /**
     * @Route("/", name="app_usuario_index", methods={"GET"})
     */
    public function index(UsuarioRepository $usuarioRepository): Response
    {
        return $this->render('usuario/index.html.twig', [
            'usuarios' => $usuarioRepository->findAll(),
        ]);
    }

    /**
     * @Route("/registrar", name="app_usuario_new", methods={"GET", "POST"})
     */
    public function new(Request $request, UsuarioRepository $usuarioRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {

        $roles = $entityManager->getRepository(Rol::class)->findAll();
        $r = [];
        foreach ($roles as $rol){
            $r[] = $rol->getNombre();
        }

        $cargos = $usuarioRepository->findDistinctCargos();
        foreach ($cargos as $key => $value){
            $r[] = $value['cargo'];
        }

        $usuarios = $usuarioRepository->findAll();
        $u = [];
        foreach ($usuarios as $usuario){
            $u[] = $usuario->getUserIdentifier();
        }
        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario, [
            'cargos' => implode(',', array_unique($r)),
            'usuarios' => implode(',', $u),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $usuario->getPassword();
            if($password){
                $hashedPassword = $passwordHasher->hashPassword(
                    $usuario,
                    $password
                );
                $usuario->setPassword($hashedPassword);
            }
            try{
                $usuarioRepository->add($usuario, true);
            } catch (UniqueConstraintViolationException $e){
                $this->notificacionError('El usuario ' . $usuario->getUserIdentifier() . ' ya está registrado');
                return $this->renderForm('usuario/new.html.twig', [
                    'usuario' => $usuario,
                    'form' => $form,
                ]);
            }

            $this->notificacionSatisfactoria('Usuario registrado satisfactoriamente');
            return $this->redirectToRoute('app_usuario_show', ['id' => $usuario->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('usuario/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_usuario_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Usuario $usuario): Response
    {
        $form = $this->createFormBuilder()
            ->add('usuario', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'userIdentifier',
                'data' => $usuario,
                'label' => 'Usuario a mostrar',
                'placeholder' => 'Seleccione',
                'attr' => [
                    'data-role' => 'select',
                    'class' => 'metro-input',
                    'data-prepend' => 'Usuario',
                    'data-on-change' => 'submit_form()'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $usuario = $form->getData()['usuario'];
            return $this->redirectToRoute('app_usuario_show', ['id' => $usuario->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_usuario_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Usuario $usuario, UsuarioRepository $usuarioRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UsuarioType::class, $usuario, ['password_required' => false]);
        $form->remove('password')->remove('confirm_password');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $usuarioRepository->add($usuario, true);
            } catch (UniqueConstraintViolationException $e){
                $this->notificacionError('El usuario ' . $usuario->getUserIdentifier() . ' ya está registrado');
                return $this->renderForm('usuario/new.html.twig', [
                    'usuario' => $usuario,
                    'form' => $form,
                ]);
            }

            $usuario->setFotoFile(null);

            $this->notificacionSatisfactoria('Usuario modificado satisfactoriamente');
            return $this->redirectToRoute('app_usuario_show', ['id' => $usuario->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('usuario/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/change_password", name="app_usuario_change_password", methods={"GET", "POST"})
     */
    public function changePassword(Request $request, Usuario $usuario, UsuarioRepository $usuarioRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        if(
            !$this->isGranted('ROLE_GESTOR') &&
            $this->getUser() !== $usuario
        ) {
            throw new AccessDeniedHttpException();
        }
        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [
                'label' => 'Contraseña',
                'empty_data' => '',
                'attr' => [
                    'data-role' => 'input',
                    'class' => 'metro-input',
                    'data-validate' => 'required'
                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmar contraseña',
                'empty_data' => '',
                'attr' => [
                    'data-role' => 'input',
                    'class' => 'metro-input',
                    'data-validate' => 'required compare=form[password]'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->getData()['password'];
            if($password){
                $hashedPassword = $passwordHasher->hashPassword(
                    $usuario,
                    $password
                );
                $usuario->setPassword($hashedPassword);
            }

            try{
                $usuarioRepository->add($usuario, true);
            } catch (UniqueConstraintViolationException $e){
                $this->notificacionError('El usuario ' . $usuario->getUserIdentifier() . ' ya está registrado');
                return $this->renderForm('usuario/new.html.twig', [
                    'usuario' => $usuario,
                    'form' => $form,
                ]);
            }

            $this->notificacionSatisfactoria('Contraseña cambiada satisfactoriamente');
            return $this->redirectToRoute('app_usuario_show', ['id' => $usuario->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('usuario/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_usuario_delete", methods={"POST"})
     */
    public function delete(Request $request, Usuario $usuario, UsuarioRepository $usuarioRepository): Response
    {
        if($usuario->getUserIdentifier() !== 'admin'){
            if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
                try{
                    $usuarioRepository->remove($usuario, true);
                    $this->notificacionSatisfactoria('Usuario ' . $usuario->getUserIdentifier() . ' eliminado satisfactoriamente');
                } catch (ForeignKeyConstraintViolationException $e){
                    $this->notificacionSatisfactoria('No se puede eliminar el usuario. Existen elementos relacionados con el usuario. Se deshabilitará en su lugar');
                    $this->notificacionExcepcion($e->getMessage());
                    return $this->redirectToRoute('app_usuario_inhabilitar', ['id' => $usuario->getId()]);
                }
            }
        }
        else {
            $this->notificacionAdvertencia('Usuario admin no se puede eliminar ni deshabilitar');
        }
        return $this->redirectToReferer();
    }

    /**
     * @Route("/{id}/disable", name="app_usuario_inhabilitar", methods={"GET"})
     */
    public function inhabilitar(Request $request, Usuario $usuario, UsuarioRepository $usuarioRepository): Response
    {
        try{
            $usuario->setActivo(false);
            $usuarioRepository->add($usuario, true);
            $this->notificacionSatisfactoria('Usuario ' . $usuario->getUserIdentifier() . ' inhabilitado satisfactoriamente');
        } catch (\Exception $ex){
            $this->notificacionError('Ha ocurrido un error al deshabilitar el usuario');
            $this->notificacionExcepcion($ex->getMessage());
        }
        return $this->redirectToReferer();
    }

    /**
     * @Route("/{id}/enable", name="app_usuario_habilitar", methods={"GET"})
     */
    public function habilitar(Request $request, Usuario $usuario, UsuarioRepository $usuarioRepository): Response
    {
        try{
            $usuario->setActivo(true);
            $usuarioRepository->add($usuario, true);
            $this->notificacionSatisfactoria('Usuario ' . $usuario->getUserIdentifier() . ' habilitado satisfactoriamente');
        } catch (\Exception $ex){
            $this->notificacionError('Ha ocurrido un error al habilitar el usuario');
            $this->notificacionExcepcion($ex->getMessage());
        }
        return $this->redirectToReferer();
    }
}
