<?php

namespace App\Repository;

use App\Entity\Permit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
//use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Permit>
 */
class PermitRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permit::class);
    }

    public function add(Permit $permit, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($this->computeDatetimeDiff($permit));
        if ($flush) {
            $this->save($permit);
        }

    }

    public function remove(Permit $permit): void
    {
        $em = $this->getEntityManager();
        $em->remove($permit);
        $em->flush();
    }

    public function save(Permit $permit): Permit
    {
        $em = $this->getEntityManager();
        $em->persist($permit);
        $em->flush();
        return $permit;
    }

    private function computeDatetimeDiff(Permit $permit): Permit
    {
        $d1 = $permit->getEndAt();
        $d2 = $permit->getStartAt();
        $diff = date_diff($d1,$d2, true);
        $permit->setDays($diff->d);
        $permit->setHours($diff->h + ($diff->i /60 ));
        return $permit;
    }


    /**
     * @param UserInterface $user
     * @return array
     */
    public function findActivePermitByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.employee = :user')
            ->andWhere('p.startAt >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.startAt', 'ASC')
            ->getQuery()
            ->getResult()
            ;

    }

    /**
     * @param array $users
     * @return UserInterface[]
     */
    public function findActiveStaffPermits(Collection $staffUsers): array
    {

        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.startAt >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.startAt', 'ASC')
            ;

        return $this->addStaffUserWhereClause($qb, $staffUsers)->getQuery()->getResult();
    }

    private function addStaffUserWhereClause(QueryBuilder $queryBuilder, ?Collection $staffUsers): QueryBuilder
    {
        if (0 == count($staffUsers) or null === $staffUsers) {
            return $queryBuilder;
        }

        $clause = $queryBuilder->expr()->orX();
        $userCount = 1;
        foreach ($staffUsers as $user) {
            $clause->add($queryBuilder->expr()->eq('p.employee' , '?'.$userCount));
            $queryBuilder->setParameter($userCount, $user);
            $userCount ++;
        }

        return $queryBuilder->andWhere($clause);
    }

    /**
     * @param UserInterface $user
     * @return Null|Permit
     * @throws \Exception
     */
    public function findActualUserPermit(UserInterface $user): ?Permit
    {
        return $this->createQueryBuilder('p')
            ->Where('p.employee = :user AND p.startAt <= :now AND p.endAt >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Europe/Rome')))
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findByInterval(mixed $start, mixed $end, UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.employee', 'u')
            ->select('p.id', 'p.startAt as start', 'p.endAt as end', '0 as allday', 'u.firstName as title')
            ->andWhere('p.startAt >= :start')
            ->andWhere('p.endAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ;

        /** @var User $user */
        if ($user->getStaffMembers())
        {
            $qb = $this->addStaffUserWhereClause($qb, $user->getStaffMembers());
        }

        return $qb->getQuery()->getScalarResult();
    }

    //    /**
    //     * @return Permit[] Returns an array of Permit objects
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

    //    public function findOneBySomeField($value): ?Permit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
