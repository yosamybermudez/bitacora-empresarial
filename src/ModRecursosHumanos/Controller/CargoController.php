<?php

namespace ModRecursosHumanos\Controller;

use App\Controller\AppController;
use ModRecursosHumanos\Entity\RecursosHumanosCargo;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/m/rec_humanos/datos_primarios/cargo")
 */
class CargoController extends AppController
{
    /**
     * @Route("/", name="app_mod_rec_humanos_cargo_list", methods={"GET"})
     */
    public function listar(RecursosHumanosCargoRepository $recursosHumanosCargoRepository): Response
    {
        return $this->render('mod_rec_humanos/cargo/list.html.twig', [
            'cargos' => $recursosHumanosCargoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/registrar", name="app_mod_rec_humanos_cargo_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTOR")
     */
    public function registrar(Request $request, RecursosHumanosCargoRepository $recursosHumanosCargoRepository, AppExtension $filter): Response
    {
        $escalaMaxima = $this->obtenerEscalaSalarialMaxima();
        $escalas = [];
        for($i = 1; $i <= $escalaMaxima; $i++){
            $escalas[$filter->convertirARomano($i)] = intval($i);
        }
        $recursosHumanosCargo = new RecursosHumanosCargo();
        $recursosHumanosCargo->setCargo(new OrganizacionCargo());
        dump($recursosHumanosCargo);
        $form = $this->createForm(RecursosHumanosCargoType::class, $recursosHumanosCargo, ['escalas' => $escalas]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recursosHumanosCargoRepository->add($recursosHumanosCargo, $this->getUser(),true);
            $this->notificacionSatisfactoria('OrganizacionCargo registrado satisfactoriamente');
            $this->eventoRegistroSatisfactorio();
            return $this->redirectToRoute('app_mod_rec_humanos_cargo_show', ['id' => $recursosHumanosCargo->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('mod_rec_humanos/cargo/new.html.twig', [
            'cargo' => $recursosHumanosCargo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_mod_rec_humanos_cargo_show", methods={"GET","POST"})
     */
    public function mostrar(RecursosHumanosCargo $recursosHumanosCargo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('cargo', EntityType::class, [
                'class' => InventarioAlmacen::class,
                'choice_label' => 'nombre',
                'data' => $recursosHumanosCargo,
                'label' => 'OrganizacionCargo a mostrar',
                'placeholder' => 'Seleccione',
                'attr' => [
                    'data-role' => 'select',
                    'class' => 'metro-input',
                    'data-prepend' => 'OrganizacionCargo',
                    'data-on-change' => 'submit_form()'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $recursosHumanosCargo = $form->getData()['cargo'];
            return $this->redirectToRoute('app_mod_rec_humanos_cargo_show', ['id' => $recursosHumanosCargo->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('mod_rec_humanos/cargo/show.html.twig', [
            'cargo' => $recursosHumanosCargo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_mod_rec_humanos_cargo_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTOR")
     */
    public function edit(Request $request, RecursosHumanosCargo $recursosHumanosCargo, RecursosHumanosCargoRepository $recursosHumanosCargoRepository): Response
    {
        $oldRecursosHumanosCargo = clone $recursosHumanosCargo;
        $form = $this->createForm(RecursosHumanosCargoType::class, $recursosHumanosCargo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recursosHumanosCargoRepository->add($recursosHumanosCargo, $this->getUser(), true);
            $this->notificacionSatisfactoria('OrganizacionCargo modificado satisfactoriamente');
            $this->eventoModificacionSatisfactoria($oldRecursosHumanosCargo);

            return $this->redirectToRoute('app_mod_rec_humanos_cargo_show', ['id' => $recursosHumanosCargo->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('mod_rec_humanos/cargo/new.html.twig', [
            'cargo' => $recursosHumanosCargo,
            'form' => $form,
        ]);
    }
}
