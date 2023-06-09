<?php

namespace ModRecursosHumanos\Controller;

use App\Controller\AppController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/m/rec_humanos/datos_primarios/escala_salarial")
 */
class EscalaSalarialController extends AppController
{
    /**
     * @Route("/", name="app_mod_rec_humanos_escala_salarial_define", methods={"GET", "POST"})
     */
    public function definir(Request $request, SistemaVariableConfiguracionRepository $sistemaVariableConfiguracionRepository): Response
    {
        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('escalaMaxima', NumberType::class, [
            'label' => 'Escala máxima',
            'data' => 1,
            'required' => false,
            'attr' => [
                'class' => 'metro-input',
                'data-role' => 'spinner'
            ],
        ]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $variable = new SistemaVariableConfiguracion('app_mod_rec_humanos_escala_salarial', $form->getData()['escalaMaxima']);
            $sistemaVariableConfiguracionRepository->add($variable, true);
            $this->notificacionRegistroSatisfactorio();
            $this->eventoRegistroSatisfactorio();
            return $this->redirectToRoute('app_mod_rec_humanos_ajustes');
        }

        return $this->render('mod_rec_humanos/escala_salarial/define.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
