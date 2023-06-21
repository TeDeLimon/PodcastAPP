<?php

namespace App\Controller;

use App\Entity\Podcast;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    private $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*Dashboard es una url protegida únicamente accesible para ROLE_ADMIN*/
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(Request $request, PaginatorInterface $paginator): Response
    {
        /*Sino tiene privilegios de ADMIN redirigimos index*/
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirectToRoute('index');

        /*Obtenemos un object query con todos los posts, independientemente del usuario*/
        $query = $this->em->getRepository(Podcast::class)->findPodcasts();
        /*Creamos una instancia del paginador, asignamos el offset y el LIMIT. Retornamos el paginador con los resultados*/
        $pagination =  $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 /*Este es el límite por página*/
        );

        return $this->render('admin/dashboard.html.twig', [
            'pagination' => $pagination
        ]);
    }

}
