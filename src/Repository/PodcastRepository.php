<?php

namespace App\Repository;

use App\Entity\Podcast;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Podcast>
 *
 * @method Podcast|null find($id, $lockMode = null, $lockVersion = null)
 * @method Podcast|null findOneBy(array $criteria, array $orderBy = null)
 * @method Podcast[]    findAll()
 * @method Podcast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PodcastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Podcast::class);
    }

    public function save(Podcast $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Podcast $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /*Analiza si el podcast pertenece al usuario que está logueado*/
    public function isPodcastOwner(Usuario $usuario, $id)
    {
        return $this->findBy(array('autor' => $usuario->getId(), 'id' => $id), array('autor' => 'ASC'), 1, 0);
    }

    /*Devuelve un object query con los campos deseados uniendo dos tablas mediante el autor, ordenado por fecha de subida DESC*/
    public function findAll()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT podcast.id, podcast.titulo, podcast.fecha_subida, podcast.descripcion, podcast.audio, podcast.imagen, usuario.nombre, usuario.apellidos
                FROM App:Podcast podcast
                JOIN podcast.autor usuario
                ORDER BY podcast.fecha_subida DESC"
            );
    }

    /*Devuelve un object query con una clausula where que ese el ID del usuario logueado, con los campos deseados uniendo dos tablas mediante el autor, ordenado por fecha de subida DESC*/
    public function findAllPodcasts(Usuario $usuario)
    {
        $id = $usuario->getId();
        /*Este método obtiene cierta parte de la información, devuelve un object query*/
        return $this->getEntityManager()
            ->createQuery(
                "SELECT podcast.id, podcast.titulo, podcast.fecha_subida, podcast.descripcion, podcast.audio, podcast.imagen, usuario.nombre, usuario.apellidos
                FROM App:Podcast podcast
                JOIN podcast.autor usuario
                WHERE usuario.id = $id
                ORDER BY podcast.fecha_subida DESC"
            );
    }

    public function findPodcasts()
    {
        /*Este método obtiene cierta parte de la información, devuelve un object query*/
        return $this->getEntityManager()
            ->createQuery(
                "SELECT podcast.id, podcast.titulo, podcast.fecha_subida, podcast.descripcion, podcast.audio, podcast.imagen, usuario.nombre, usuario.apellidos, usuario.email
                FROM App:Podcast podcast
                JOIN podcast.autor usuario
                ORDER BY podcast.fecha_subida DESC"
            );
    }

    public function getFourElements()
    {
        /*Este método obtiene 4 elementos que están destinados al carrousel, retorna un array con los elementos*/
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('podcast.id, podcast.titulo, podcast.fecha_subida, podcast.descripcion, podcast.audio, podcast.imagen, usuario.nombre, usuario.apellidos')
            ->from('App:Podcast', 'podcast')
            ->join('podcast.autor', 'usuario')
            ->orderBy('podcast.fecha_subida', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
    }

    public function obtenerPodcast($id)
    {
        /*Este método obtiene 1 único elemento basado en el ID*/
        return $this->getEntityManager()
            ->createQuery(
                "SELECT podcast.id, podcast.titulo, podcast.fecha_subida, podcast.descripcion, podcast.audio, podcast.imagen, usuario.nombre, usuario.apellidos
                FROM App:Podcast podcast
                JOIN podcast.autor usuario
                WHERE podcast.id = $id"
            )
            ->getResult();
    }

    public function obtenerPodcast2($id)
    {
        /*Este método obtiene 1 único elemento basado en el ID*/
        return $this->getEntityManager()
            ->createQuery(
                "SELECT podcast.id, podcast.titulo, podcast.fecha_subida, podcast.descripcion, podcast.audio, podcast.imagen
                FROM App:Podcast podcast
                WHERE podcast.autor = $id"
            )
            ->getResult();
    }





    //    /**
    //     * @return Podcast[] Returns an array of Podcast objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Podcast
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
