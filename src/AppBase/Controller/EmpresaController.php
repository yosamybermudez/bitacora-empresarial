<?php

namespace AppBase\Controller;

use App\Controller\AppController;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/base/empresa")
 */
class EmpresaController extends AppController
{
    /**
     * @Route("/{tipo}", name="app_organizacion_index", priority="10", methods={"GET"})
     */
    public function index(OrganizacionRepository $organizacionRepository, string $tipo = ''): Response
    {
        $path = $this->obtenerEnlaceActual();

        dd($path);
        //Tipo: Cliente o Proveedor
        switch ($tipo){
            case 'cliente': {
                $organizaciones = $organizacionRepository->findBy(['esCliente' => true], ['nombre' => 'ASC']);
                break;
            }
            case 'proveedor': {
                $organizaciones = $organizacionRepository->findBy(['esProveedor' => true], ['nombre' => 'ASC']);
                break;
            }
            default: {
                $this->notificacionError('Ha ocurrido un error inesperado al acceder al enlace. Se mostrarán los clientes');

                return $this->redirectToRoute('app_organizacion_index', ['tipo' => 'cliente']);
            }
        }

        return $this->render('organizacion/index.html.twig', [
            'tipo' => $tipo,
            'organizaciones' => $organizaciones,
        ]);
    }

    /**
     * @Route("/{tipo}/{id}", name="app_organizacion_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Organizacion $organizacion, string $tipo): Response
    {
        $tipo = 'organización';
        if($organizacion->isEsProveedor()){
            $tipo = 'proveedor';
        }
        if($organizacion->isEsCliente()){
            $tipo = 'cliente';
        }

        $form = $this->createFormBuilder()
            ->add('organizacion', EntityType::class, [
                'class' => Organizacion::class,
                'choice_label' => 'nombre',
                'data' => $organizacion,
                'label' => ucfirst($tipo) . ' a mostrar',
                'placeholder' => 'Seleccione',
                'attr' => [
                    'data-role' => 'select',
                    'class' => 'metro-input',
                    'data-prepend' => ucfirst($tipo),
                    'data-on-change' => 'submit_form()'
                ],
                'query_builder' => function (EntityRepository $er) use ($organizacion) {
                    return $er->createQueryBuilder('cliente_proveedor')
                        ->where('cliente_proveedor.esProveedor = :proveedor')
                        ->andWhere('cliente_proveedor.esCliente = :cliente')
                        ->setParameter('cliente', $organizacion->esCliente())
                        ->setParameter('proveedor', $organizacion->esProveedor())
                        ->orderBy('cliente_proveedor.nombre', 'ASC');
                },
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $organizacion = $form->getData()['organizacion'];
            return $this->redirectToRoute('app_organizacion_show', ['id' => $organizacion->getId(), 'tipo' => $tipo], Response::HTTP_SEE_OTHER);
        }
        return $this->render('organizacion/show.html.twig', [
            'organizacion' => $organizacion,
            'form' => $form->createView(),
            'tipo' => $tipo
        ]);
    }

    /**
     * @Route("/{tipo}/{id}/edit", name="app_organizacion_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Organizacion $organizacion, OrganizacionRepository $organizacionRepository, string $tipo): Response
    {
        $oldOrganizacion = clone $organizacion;
        $form = $this->createForm(OrganizacionType::class, $organizacion);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $organizacionRepository->add($organizacion, $this->getUser(),true);
            $this->notificacionSatisfactoria('Datos del ' . $tipo . ' modificados satisfactoriamente');
            $this->eventoModificacionSatisfactoria($oldOrganizacion);
            return $this->redirectToRoute('app_organizacion_show', ['id' => $organizacion->getId(), 'tipo' => $tipo]);
        }

        return $this->renderForm('organizacion/new.html.twig', [
            'tipo' => $tipo,
            'organizacion' => $organizacion,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/current", name="app_organizacion_current_show", priority="20", methods={"GET"})
     */
    public function showCurrent(OrganizacionRepository $organizacionRepository): Response
    {
        return $this->render('organizacion/show.html.twig', [
            'organizacion' => $organizacionRepository->findBy(['esMiOrganizacion' => true])[0],
            'tipo' => ''
        ]);
    }

    /**
     * @Route("/current/edit", name="app_organizacion_current_edit", priority="20", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTOR")
     */
    public function editCurrent(Request $request, OrganizacionRepository $organizacionRepository): Response
    {
        $organizacion = $organizacionRepository->findBy(['esMiOrganizacion' => true])[0];
        $oldOrganizacion = clone $organizacion;
        $form = $this->createForm(OrganizacionType::class, $organizacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventoModificacionSatisfactoria($oldOrganizacion);
            $organizacionRepository->add($organizacion, $this->getUser(), true);

            $this->notificacionSatisfactoria('Datos de la organización modificados satisfactoriamente');
            return $this->redirectToRoute('app_organizacion_current_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('organizacion/new.html.twig', [
            'organizacion' => $organizacion,
            'form' => $form,
            'tipo' => ''
        ]);
    }

    /**
     * @Route("/{tipo}/registrar", name="app_organizacion_new", priority="15", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_EDITOR') or is_granted('ROLE_EDITOR')")
     */
    public function new(Request $request, OrganizacionRepository $organizacionRepository, string $tipo): Response
    {
        $organizacion = new Organizacion();
        //Tipo: Cliente o Proveedor
        switch ($tipo){
            case 'cliente': {
                $organizacion->setEsCliente(true);
                break;
            }
            case 'proveedor': {
                $organizacion->setEsProveedor(true);
                break;
            }
        }
        $form = $this->createForm(OrganizacionType::class, $organizacion);
        //Tipo: Cliente o Proveedor
        switch ($tipo){
            case 'cliente': {
                $form->remove('esCliente');
                break;
            }
            case 'proveedor': {
                $form->remove('esProveedor');
                break;
            }
        }
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            //Tipo: Cliente o Proveedor
            switch ($tipo){
                case 'cliente': {
                    $organizacion->setEsCliente(true);
                }
                case 'proveedor': {
                    $organizacion->setEsProveedor(true);
                }
            }
            $this->eventoRegistroSatisfactorio();
            $organizacionRepository->add($organizacion, $this->getUser(),true);
            $this->notificacionSatisfactoria('Datos del ' . $tipo . ' registrados satisfactoriamente');
            return $this->redirectToRoute('app_organizacion_show', ['id' => $organizacion->getId(), 'tipo' => $tipo], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('organizacion/new.html.twig', [
            'tipo' => $tipo,
            'organizacion' => $organizacion,
            'form' => $form,
        ]);
    }
}
