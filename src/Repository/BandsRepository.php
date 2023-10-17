<?php

namespace App\Repository;

use App\Entity\Bands;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bands>
 *
 * @method Bands|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bands|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bands[]    findAll()
 * @method Bands[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BandsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bands::class);
    }

    public function add(Bands $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Bands $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getConnection()
    {
        return $this->getEntityManager()->getConnection();
    }
}
