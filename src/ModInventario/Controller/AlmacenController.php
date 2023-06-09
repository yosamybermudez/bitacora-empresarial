<?php

namespace ModInventario\Controller;

use App\Controller\AppController;
use ModInventario\Entity\Almacen;
use ModInventario\Entity\AlmacenProducto;
use ModInventario\Form\AlmacenType;
use ModInventario\Repository\AlmacenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/almacen")
 */
class AlmacenController extends AppController
{
    /**
     * @Route("/", name="app_almacen_index", methods={"GET"})
     */
    public function index(AlmacenRepository $almacenRepository): Response
    {
        return $this->render('almacen/index.html.twig', [
            'almacens' => $almacenRepository->findAll(),
        ]);
    }

    /**
     * @Route("/registrar", name="app_almacen_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function new(Request $request, AlmacenRepository $almacenRepository): Response
    {
        //Generar el codigo del movimiento. Ejemplo: ALM0001
        $codigo = 'ALM' . $this->numeroConCeros(count($almacenRepository->findAll()) + 1) ;
        $almacen = new Almacen();
        $almacen->setCodigo($codigo);
        $form = $this->createForm(AlmacenType::class, $almacen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $almacenRepository->add($almacen, $this->getUser(),true);
            $this->notificacionSatisfactoria('Almacén registrado satisfactoriamente');
            $this->eventoRegistroSatisfactorio();
            return $this->redirectToRoute('app_almacen_show', ['id' => $almacen->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('almacen/new.html.twig', [
            'almacen' => $almacen,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_almacen_show", methods={"GET","POST"})
     */
    public function show(Almacen $almacen, SerializerInterface $serializer, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('almacen', EntityType::class, [
                'class' => Almacen::class,
                'choice_label' => 'nombre',
                'data' => $almacen,
                'label' => 'Almacén a mostrar',
                'placeholder' => 'Seleccione',
                'attr' => [
                    'data-role' => 'select',
                    'class' => 'metro-input',
                    'data-prepend' => 'Almacén',
                    'data-on-change' => 'submit_form()'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $almacen = $form->getData()['almacen'];
            return $this->redirectToRoute('app_almacen_show', ['id' => $almacen->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('almacen/show.html.twig', [
            'almacen' => $almacen,
            'form' => $form->createView(),
            'almacen_productos' => $entityManager->getRepository(AlmacenProducto::class)->findBy(['almacen' => $almacen->getId()])
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_almacen_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function edit(Request $request, Almacen $almacen, AlmacenRepository $almacenRepository): Response
    {
        $oldAlmacen = clone $almacen;
        $form = $this->createForm(AlmacenType::class, $almacen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $almacenRepository->add($almacen, $this->getUser(), true);
            $this->notificacionSatisfactoria('Almacén modificado satisfactoriamente');
            $this->eventoModificacionSatisfactoria($oldAlmacen);

            return $this->redirectToRoute('app_almacen_show', ['id' => $almacen->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('almacen/new.html.twig', [
            'almacen' => $almacen,
            'form' => $form,
        ]);
    }

}
