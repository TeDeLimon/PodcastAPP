<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class LoginController extends AbstractController
{

    private $em;
    /**
     * @param $em
     */
    /*Inicializamos una Manejador de Entidades*/
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*Realizamos control de autentificación declarando en security el login_path y los parámetros del controlador, que se va a encargar de procesar los datos*/
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticacionUtils): Response
    {
        /*En caso de quere volver a loguear y teniendo como condición que tiene un rol asignado, se le redirige de acuerdo a especificaciones*/
        if($this->isGranted('ROLE_ADMIN'))  return $this->redirectToRoute('dashboard');
        
        if($this->isGranted('ROLE_USER'))   return $this->redirectToRoute('podcasts');

        //Obtiene un error en el login en caso de existir
        $error = $authenticacionUtils->getLastAuthenticationError();

        //Obtiene el username auténticado*/
        $lastUserName = $authenticacionUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'error' => $error,
            'lastUserName' => $lastUserName,
        ]);
    }

    /* Registro de nuevos usuarios, usando el Form UsuarioType y el createForm.*/
    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, UserAuthenticatorInterface $userAuthenticator, FormLoginAuthenticator $formLoginAuthenticator, string $alerta = ''): Response
    {

        $usuario = new Usuario();
        /*Guardamos en una variable el objeto Form a partir de la clase UsuarioType*/
        $form = $this->createForm(UsuarioType::class, $usuario);

        /*Asociamos al formulario los datos obtenidos del Request*/
        $form->handleRequest($request);

        /*Si el formulario está enviado mediante POST y es válido*/
        if ($form->isSubmitted() && $form->isValid()) {

            /*Comprobamos que no existe el usuario en la base de datos*/
            $email = $form->get('email')->getData();
            $existeUsuario = $this->em->getRepository(Usuario::class)->existeUsuario($email);
            /*En caso de existir debemos lanzar un mensaje amistoso*/
            if (!$existeUsuario) {

                /*Obtenemos los datos planos de password*/
                $plainTextPassword = $form->get('password')->getData();
                /*Hasheamos la contraseña, usamos el usuario y la contraseña plana*/
                $hashedPassword = $passwordHasher->hashPassword($usuario, $plainTextPassword);
                /*Establecemos la contraseña hasheada y rol al user*/
                $usuario->setPassword($hashedPassword);
                /*La creación de administradores solo se va a contemplar desde la base de datos*/
                $usuario->setRoles(['ROLE_USER']);
                /*Lo almacenamos en la BBDD*/
                $this->em->persist($usuario);
                $this->em->flush();

                /*Al crear en la base de datos un nuevo registro, debemos autentificarlo manualmente*/
                $userAuthenticator->authenticateUser($usuario, $formLoginAuthenticator, $request);
                return $this->redirectToRoute('podcasts');
            }
            $alerta = 'Usuario ya registrado en la base de datos';
        }

        return $this->render('login/register.html.twig', [
            'form' => $form->createView(),
            'alerta' => $alerta
            //'user'  => $this->getUser(),
        ]);
    }

    /*Implementada de forma minimalista, logout se controla desde el security.yaml indicando el path que va a encargarse*/
    #[Route('/logout', name: 'logout')]
    public function logout()
    {
    }

    /*Función en desarrollo para la creación de ADMIN, actualmente no se contempla*/
    #[Route('/newAdmin', name: 'newAdmin')]
    public function newAdmin(): Response
    {
        return $this->render('login/newAdmin.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }
}
