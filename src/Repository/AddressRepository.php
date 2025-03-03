<?php

namespace App\Repository;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\User;
use App\Enum\EventStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Address>
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    public function findByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Address[] Returns an array of Address objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Address
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function banAddress(Address $address, EntityManagerInterface $em): void
    {
       ;
        if ($address->isAllowed()) {
            $address->setIsAllowed(false);


            $events = $address->getEvents();
            foreach ($events as $event) {
                $event->setAddress(null);
                $event->setStatus(EventStatus::CANCELLED);
                $event->setCancelReason("L'addresse de la sortie n'est pas authorisée");
            }
        } else {
            $address->setIsAllowed(true);
        }


        $em->persist($address);
        $em->flush();
    }

    }
