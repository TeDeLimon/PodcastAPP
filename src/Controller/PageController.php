<?php

namespace App\Controller;

use App\Entity\Podcast;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PageController extends AbstractController
{

    private $em;

    /**
     * @param PageController se encarga de controlar las páginas que no requieren de autentificación y por tanto no se asocian a usuarios
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*Vista index o raíz como tal*/
    #[Route('/', name: 'index')]
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        /*Obtenemos los cuatro elementos más recientes*/
        $podcastCarousel = $this->em->getRepository(Podcast::class)->getFourElements();
        /*Creamos un Object Query que será utilizado por el paginador*/
        $query = $this->em->getRepository(Podcast::class)->findAll();
        
        $podcasts =  $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15 /*Este es el límite por página*/
        );

        return $this->render('page/index.html.twig', [
            'podcastCarousel' => $podcastCarousel,
            'podcasts' => $podcasts
        ]);
    }

    /*Simple vista con un diseño minimalista para informarse*/
    #[Route('/about', name: 'about')] 
    public function about() {
        return $this->render('page/about.html.twig', [

        ]);
    }
}
