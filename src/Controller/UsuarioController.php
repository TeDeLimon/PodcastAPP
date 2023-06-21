<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\Usuario;
use App\Form\UsuarioType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class UsuarioController extends AbstractController
{

    private $em;

    /**
     * Este controlador quise separarlo de métodos como login, logout, register debido a que no solo intervienen métodos y clases estrictos al usuario
     * y dejar especializado el controlador LOGIN a este tipo de acciones
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/podcasts', name: 'podcasts')]
    public function podcasts(Request $request, PaginatorInterface $paginator): Response
    {

        /*Cualquier ROLE de los actuales puede acceder a la sección de podcasts dado que consideramos que un administrador también puede subir sus propios Podcasts*/
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /*Obtenemos todos los podcasts con el usuario que actualmente está logueado*/
        $query = $this->em->getRepository(Podcast::class)->findAllPodcasts($this->getUser());
        /*Generamos el paginador*/
        $pagination =  $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 /*Este es el límite por página*/
        );

        return $this->render('usuario/podcasts.html.twig', [
            'pagination' => $pagination
        ]);
    }

    // Método para ver un usuario existente, recibe un ID que es el usuario a ver
    #[Route('/usuario/view/{id}', name: 'viewUsuario')]
    public function view(Usuario $usuario): Response
    {
        /*Comprobamos que se haya autenticado como ADMIN*/
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('usuario/view.html.twig', [
            'usuario' => $usuario
        ]);
    }

    // Método para ver un usuario existente, recibe un ID que es el usuario a ver
    #[Route('/usuario/crear', name: 'crearUsuario')]
    public function crearUsuario(Request $request, UserPasswordHasherInterface $passwordHasher, string $alerta = ''): Response
    {
        /*Comprobamos que se haya autenticado como ADMIN*/
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

                return $this->redirectToRoute('dashboardUsuario');
            }
            $alerta = 'Usuario ya registrado en la base de datos';
        }

        return $this->render('usuario/crear.html.twig', [
            'form' => $form->createView(),
            'alerta' => $alerta
        ]);
    }

    // Método para ver un usuario existente, recibe un ID que es el usuario a actualizar
    #[Route('/usuario/update/{id}', name: 'updateUsuario')]
    public function updateUsuario(Request $request, UserPasswordHasherInterface $passwordHasher, Usuario $usuario): Response
    {
        /*Comprobamos que se haya autenticado como ADMIN*/
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /*Guardamos en una variable el objeto Form a partir de la clase UsuarioType*/
        $form = $this->createForm(UsuarioType::class, $usuario);

        /*Asociamos al formulario los datos obtenidos del Request*/
        $form->handleRequest($request);

        /*Si el formulario está enviado mediante POST y es válido*/
        if ($form->isSubmitted() && $form->isValid()) {

            /*Obtenemos los datos planos de password*/
            $plainTextPassword = $form->get('password')->getData();
            /*Hasheamos la contraseña, usamos el usuario y la contraseña plana*/
            $hashedPassword = $passwordHasher->hashPassword($usuario, $plainTextPassword);
            /*Establecemos la contraseña hasheada y rol al user*/
            $usuario->setPassword($hashedPassword);
            /*La creación de administradores solo se va a contemplar desde la base de datos*/
            $usuario->setRoles(['ROLE_USER']);
            $this->em->flush();

            return $this->redirectToRoute('dashboardUsuario');
        }

        return $this->render('usuario/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

     // Método para eliminar un usuario basado en el ID
     #[Route('/usuario/delete/{id}', name: 'deleteUsuario')]
     public function deleteUsuario(Usuario $usuario): Response
     {
         /*Comprobamos que se haya autenticado como ADMIN*/
         $this->denyAccessUnlessGranted('ROLE_ADMIN');
 
         /*Comprueba si tiene ROLE de ADMIN. De cumplir eliminamos el registro*/
         if ($this->isGranted('ROLE_ADMIN')) $this->em->getRepository(Usuario::class)->remove($usuario, true);
 
         return $this->redirectToRoute('dashboardUsuario'); // Redirección a podcasts por defecto
     }

    /*Esta url del controlador muestra información básica de la cuenta del usuario logueado*/
    #[Route('/cuenta', name: 'cuenta')]
    public function cuenta(): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('usuario/cuenta.html.twig', []);
    }

    /*Este método estaría encargado de generar un token de recuperación de claves y enviarlo por correo.*/
    #[Route('/recover', name: 'recover')]
    public function recover(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('usuario/recover.html.twig', []);
    }
}
