<?php

namespace App\Controller;

use Exception;
use App\Entity\Podcast;
use App\Form\PodcastType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PodcastController extends AbstractController
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

    // Método para crear un nuevo podcast
    #[Route('/podcast/create', name: 'create')]
    public function create(Request $request, SluggerInterface $slugger): Response
    {
        /*Analiza si está totalmente autenticado*/
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /*Creamos una instancia de la clase Form indicando en base al podcast*/
        $podcast = new Podcast();

        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*En caso de haberse enviado y tener todos los datos válidos*/

            $audio = $form->get('audio')->getData();

            /*Si existe el audio modificamos su nombre generando un nombre seguro*/
            if ($audio) {
                $audioOriginal = pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME);
                $audioSafe = $slugger->slug($audioOriginal);
                $audioModificado =  $audioSafe . '-' . uniqid() . '.' . $audio->guessExtension();

                // Movemos el archivo a un directorio del servidor
                try {
                    $audio->move(
                        $this->getParameter('audio_directory'),
                        $audioModificado
                    );
                } catch (FileException $e) {
                    throw new Exception(message: 'Ha habido un problema al subir el audio');
                }

                $podcast->setAudio($audioModificado);
            }

            /*Seguimos el mismo proceso para la imagen, ¿Quizás podríamos simplificar esto en una clase?*/
            $image = $form->get('imagen')->getData();

            if ($image) {
                $imagenOriginal = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $imagenSafe = $slugger->slug($imagenOriginal);
                $imagenModificada =  $imagenSafe . '-' . uniqid() . '.' . $image->guessExtension();

                // Move the file to the directory where files are stored
                try {
                    $image->move(
                        $this->getParameter('image_directory'),
                        $imagenModificada
                    );
                } catch (FileException $e) {
                    throw new Exception(message: 'Ha habido un problema al subir la imagen');
                }

                $podcast->setImagen($imagenModificada);
            }

            $podcast->setAutor($this->getUser());
            // Asignar el usuario actual como autor del podcast

            $this->em->persist($podcast);

            $this->em->flush();

            return $this->redirectToRoute('podcasts'); // Redirige a la página deseada después de crear el podcast
        }

        return $this->render('podcast/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Método para actualizar un podcast existente, recibe un ID que es el podcast a ver
    #[Route('/podcast/view/{id}', name: 'view')]
    public function view(PaginatorInterface $paginator, Request $request, Podcast $podcast): Response
    {
        $query = $this->em->getRepository(Podcast::class)->findAll();
        $podcasts =  $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15 /*Este es el límite por página*/
        );

        $podcast = $this->em->getRepository(Podcast::class)->obtenerPodcast($podcast->getId());

        return $this->render('podcast/view.html.twig', [
            'podcast' => $podcast[0],
            'podcasts' => $podcasts
        ]);
    }

    // Método para eliminar un podcast
    #[Route('/podcast/delete/{id}', name: 'delete')]
    public function delete(Podcast $podcast): Response
    {
        /*Comprobamos que se haya autenticado correctamente*/
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /*Comprobamos que el usuario auténticado sea propietario del podcast*/
        $isPodcastOwner =  $this->em->getRepository(Podcast::class)->isPodcastOwner($this->getUser(), $podcast->getId());

        /*Comprueba si es el propietario del Podcast o que si por el contrario tiene ROLE de ADMIN. De cumplir eliminamos el registro*/
        if ($isPodcastOwner || $this->isGranted('ROLE_ADMIN')) $this->em->getRepository(Podcast::class)->remove($podcast, true);

        return $this->redirectToRoute('podcasts'); // Redirección a podcasts por defecto
    }

    // Método para actualizar un podcast existente
    #[Route('/podcast/update/{id}', name: 'update')]
    public function update(Request $request, SluggerInterface $slugger, Podcast $podcast): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /*Comprobamos que el usuario auténticado sea propietario del podcast, sino lo es redireccionamos*/
        $isPodcastOwner =  $this->em->getRepository(Podcast::class)->isPodcastOwner($this->getUser(), $podcast->getId());

        if (!$isPodcastOwner) return $this->redirectToRoute('podcasts');

        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        /*Creamos una instancia del Manejador de Archivos del sistema*/
        $filesystem = new Filesystem();

        /*Si el formulario ha sido enviado y los datos son válidos*/
        if ($form->isSubmitted() && $form->isValid()) {

            $audioFile = $form->get('audio')->getData();
            $imageFile = $form->get('imagen')->getData();

            if ($imageFile) {
                /*Eliminamos el archivo del directorio en cuestión*/
                $filesystem->remove($this->getParameter('image_directory') . '/' . $podcast->getImagen());

                $imagenOriginal = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $imagenSafe = $slugger->slug($imagenOriginal);
                $imagenModificada =  $imagenSafe . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $imagenModificada
                    );
                } catch (FileException $e) {
                    throw new Exception(message: 'Ha habido un problema al subir la imagen');
                }

                $podcast->setImagen($imagenModificada);
            }

            /*Realizamos el mismo paso para el archivo de audio el cual si es obligatorio*/
            if ($audioFile) {
                $filesystem->remove($this->getParameter('audio_directory') . '/' . $podcast->getAudio());

                $audioOriginal = pathinfo($audioFile->getClientOriginalName(), PATHINFO_FILENAME);
                $audioSafe = $slugger->slug($audioOriginal);
                $audioModificado =  $audioSafe . '-' . uniqid() . '.' . $audioFile->guessExtension();

                // Move the file to the directory where files are stored
                try {
                    $audioFile->move(
                        $this->getParameter('audio_directory'),
                        $audioModificado
                    );
                } catch (FileException $e) {
                    throw new Exception(message: 'Ha habido un problema al subir la imagen');
                }

                $podcast->setAudio($audioModificado);
            }

            $this->em->flush();

            return $this->redirectToRoute('podcasts'); // Redirige a la página deseada después de actualizar el podcast
        }

        return $this->render('podcast/update.html.twig', [
            'form' => $form->createView(),
            'podcast' => $podcast,
        ]);
    }
}
