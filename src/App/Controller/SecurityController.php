<?php

namespace App\Controller;

use App\Form\DatosInicialesType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/app/security")
 */
class SecurityController extends AppController
{
    private function remaining(string $element, string $parameter){

        switch ($parameter){
            case 'y': {
                $element .= (int)$element <= 0 ? '' : ((int)$element === 1 ? ' año' : ' años');
                break;
            }
            case 'm': {
                $element .= (int)$element <= 0 ? '' : ((int)$element === 1 ? ' mes' : ' meses');
                break;
            }
            case 'd': {
                $element .= (int)$element <= 0 ? '' : ((int)$element === 1 ? ' día' : ' días');
                break;
            }
            default: {
                break;
            }
        }
        return $element === '0' ? null : $element;
    }

    /**
     * @Route("/install/database_create/execute", name="app_install_database_create_execute")
     */
    public function databaseCreateExecute(KernelInterface $kernel, EntityManagerInterface $entityManager): Response
    {
        try{
            $isInstalled = $entityManager->getRepository(VariableConfiguracion::class)->findOneByNombre('app_installed');
            if($isInstalled){
                return $this->redirectToRoute('app_install_force');
            }
        } catch (\Exception $e){

        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        try{
            // Drop old database
            $options = array('command' => 'doctrine:database:drop', '--force' => true);
            $application->run(new ArrayInput($options));
            $entityManager->getConnection()->close();
        } catch (\Exception $e){

        }

        try{
            // Create new database
            $options = array('command' => 'doctrine:database:create');
            $application->run(new ArrayInput($options));

            // Update schema
            $options = array('command' => 'doctrine:schema:update','--force' => true);
            $application->run(new ArrayInput($options));

            // Loading Fixtures, --append option prevent confirmation message
            $options = array('command' => 'doctrine:fixtures:load','--append' => true);
            $application->run(new ArrayInput($options));
        } catch (\Exception $e){
            $this->notificacionError('Ocurrió un error inesperado. Volviendo a empezar');
            return $this->redirectToRoute('app_install_database_create_execute');
        }
        return $this->redirectToRoute('app_install_database_create');
    }

    /**
     * @Route("/install/database_create", name="app_install_database_create")
     */
    public function databaseCreate(
        KernelInterface $kernel,
        Connection $connection,
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $params = $connection->getParams();
        $options['dbname'] = $params['dbname'];
        $options['host'] = $params['host'];
        $options['port'] = $params['port'];
        $form = $this->createForm(DatosInicialesType::class, [], $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                //Administrador del sistema
                $usuario = new Usuario();
                $usuario->setUsername($form->getData()['username']);
                $usuario->setRoles(array('ROLE_ADMINISTRADOR_SISTEMA'));
                $hashedPassword = $passwordHasher->hashPassword(
                    $usuario,
                    $form->getData()['username']
                );
                $usuario->setPassword($hashedPassword);

                //Organizacion
                $organizacion = new Organizacion();
                $organizacion->setNombre($form->getData()['organizacion_nombre']);
                $organizacion->setSiglas($form->getData()['organizacion_siglas']);
                $organizacion->setEsMiOrganizacion(true);
                if(isset($form->getData()['organizacion_logo'])){
                    $organizacion->setLogoFile($form->getData()['organizacion_logo']);
                    $organizacion->setLogo($form->getData()['organizacion_logo']->getClientOriginalName());
                }

                $entityManager->getRepository(Usuario::class)->add($usuario, true);
                $entityManager->getRepository(Organizacion::class)->add($organizacion, $usuario, true);

                //Licencia
                $license_key = new VariableConfiguracion('license_key', $this->generateStarterLicense());
                $entityManager->getRepository(VariableConfiguracion::class)->add($license_key, true);


            } catch (\Exception $e) {
                $this->notificacionSatisfactoria('Ocurrió un error');
                return $this->redirectToRoute('app_security_login');
            }
            $app_installed = new VariableConfiguracion('app_installed', 1);
            $entityManager->getRepository(VariableConfiguracion::class)->add($app_installed, true);

            $this->notificacionSatisfactoria('Datos cargados satisfactoriamente');
            return $this->redirectToRoute('app_sistema_modulo_activation');


        }

        return $this->renderForm('app/security/load_data.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/reset", name="app_reset", methods={"GET","POST"})
     */
    public function reset(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('clave', PasswordType::class, [
                'label' => 'Clave',
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                    'data-validate' => 'required',
                    'value' => '9661de633bdd04305d84f23b2c2c893e4f6c6d2a'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clave = '9661de633bdd04305d84f23b2c2c893e4f6c6d2a';

            try{
                $encodedPassword = sha1($form->getData()['clave']);

                $isInstalled = $entityManager->getRepository(VariableConfiguracion::class)->findOneByNombre('app_installed');
                if($clave === $form->getData()['clave']){
                    if($isInstalled){
                        $entityManager->getRepository(VariableConfiguracion::class)->remove($isInstalled, true);
                    }
                    return $this->redirectToRoute('app_install_database_create_execute');
                } else {
                    $this->notificacionSatisfactoria('Clave incorrecta');
                    return $this->redirectToRoute('app_reset');
                }

            } catch (\Exception $e) {
                dd($e->getMessage());
                $this->notificacionSatisfactoria('Ocurrió un error');
                return $this->redirectToRoute('app_security_login');
            }
        }

        return $this->renderForm('app/security/force.html.twig', [
            'form' => $form,
        ]);

    }

//    /**
//     * @Route("/install/load_data", name="app_install_load_data", methods={"GET","POST"})
//     */
//    public function loadData(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
//    {
//        $form = $this->createForm(DatosInicialesType::class, []);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            try{
//                //Administrador del sistema
//                $usuario = new Usuario();
//                $usuario->setUsername($form->getData()['identificador']);
//                $usuario->setRoles(array('ROLE_ADMINISTRADOR_SISTEMA'));
//                $hashedPassword = $passwordHasher->hashPassword(
//                    $usuario,
//                    $form->getData()['password']
//                );
//                $usuario->setPassword($hashedPassword);
//
//                //Organizacion
//                $organizacion = new Organizacion();
//                $organizacion->setNombre($form->getData()['empresa_nombre']);
//                $organizacion->setEsMiOrganizacion(true);
//                if(isset($form->getData()['empresa_logo'])){
//                    $organizacion->setLogoFile($form->getData()['empresa_logo']);
//                    $organizacion->setLogo($form->getData()['empresa_logo']->getClientOriginalName());
//                }
//
//                $entityManager->getRepository(Usuario::class)->add($usuario, true);
//                $entityManager->getRepository(Organizacion::class)->add($organizacion, $usuario, true);
//
//                dd($entityManager->getRepository(Organizacion::class)->findAll());
//
//                //Licencia
//                $license_key = new VariableConfiguracion('license_key', $this->generateLicense());
//                $entityManager->getRepository(VariableConfiguracion::class)->add($license_key, true);
//
//
//            } catch (\Exception $e) {
//                dd($e->getMessage());
//                $this->notificacionSatisfactoria('Ocurrió un error');
//                return $this->redirectToRoute('app_security_login');
//            }
//            $app_installed = new VariableConfiguracion('app_installed', 1);
//            $entityManager->getRepository(VariableConfiguracion::class)->add($app_installed, true);
//
//            $this->notificacionSatisfactoria('Datos cargados satisfactoriamente. Inicie sesión');
//            dump('Success');
//            return $this->redirectToRoute('app_security_login');
//
//        }
//
//        return $this->renderForm('app/security/load_data.html.twig', [
//            'form' => $form,
//        ]);
//
//    }

//    /**
//     * @Route("/install", name="app_install")
//     */
//    public function install(): Response
//    {
//        return $this->redirectToRoute('app_install_database_create');
//    }

    /**
     * @Route("/login/license", name="app_login_license", methods={"GET", "POST"})
     */
    public function loginLicense(Request $request): Response
    {
        $license = $this->getLicense();
        $license['request_code'] = $this->generateRequestCode();

        $form = $this->createFormBuilder()
            ->add('activation_code', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                    'data-validate' => 'required',
                    'data-custom-buttons' => 'customButtons_2'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $activation_code = $form->getData()['activation_code'];
            $new = $this->checkLicense($activation_code);
            if($new && $new['status'] === 'valid'){
                $this->setLicense($activation_code);
                $this->notificacionSatisfactoria('La licencia ha sido actualizada satisfactoriamente');
            }
            else {
                $this->notificacionSatisfactoria('La licencia no es válida');
            }

            return $this->redirectToRoute('app_security_login');
        }



        return $this->renderForm('app/security/license_request.html.twig', [
            'license' => $license,
            'form' => $form
        ]);
    }

    /**
     * @Route("/license", name="app_license", methods={"GET", "POST"})
     */
    public function license(Request $request): Response
    {
        $license = $this->getLicense();

        $form = $this->createFormBuilder()
            ->add('activation_code', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'metro-input',
                    'data-role' => 'input',
                    'data-validate' => 'required',
                    'data-custom-buttons' => 'customButtons_2'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            dd($form->getData());

            $activation_code = $form->getData()['activation_code'];
            $new = $this->checkLicense($activation_code);
            if($new && $new['status'] === 'valid'){
                $this->setLicense($activation_code);
                $this->notificacionSatisfactoria('La licencia ha sido actualizada satisfactoriamente');
            }
            else {
                $this->notificacionSatisfactoria('La licencia no es válida');
            }

            return $this->redirectToRoute('app_license');
        }



        return $this->renderForm('app/security/license.html.twig', [
            'license' => $license,
            'form' => $form
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/install/database_error", name="app_install_database_error")
     */
    public function databaseError(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
