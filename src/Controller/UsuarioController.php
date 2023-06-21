<?php

namespace App\Controller;

use App\Entity\Podcast;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    /*Esta url del controlador muestra información básica de la cuenta del usuario logueado*/
    #[Route('/cuenta', name: 'cuenta')]
    public function cuenta(): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('usuario/cuenta.html.twig', [
        ]);
    }

    /*Este método estaría encargado de generar un token de recuperación de claves y enviarlo por correo.*/
    #[Route('/recover', name: 'recover')]
    public function recover(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('usuario/recover.html.twig', [
        ]);
    }
}
