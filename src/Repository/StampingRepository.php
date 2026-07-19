<?php

namespace App\Repository;

use App\Entity\Stamping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stamping>
 */
class StampingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stamping::class);
    }

    public function add(Stamping $stamping): Stamping
    {
        $em = $this->getEntityManager();
        $em->persist($stamping);
        $this->save($stamping);

        return $stamping;
    }

    public function save(Stamping $stamping): Stamping
    {
        $em = $this->getEntityManager();
        $em->persist($stamping);
        $em->flush($stamping);

        return $stamping;
    }

    public function remove(Stamping $stamping): void
    {
        $em = $this->getEntityManager();
        $em->remove($stamping);
        $em->flush();
    }

    public function findStaffMissedStamping(Collection $staffUsers, string $status): array
    {

        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.missedAt', 'DESC')
        ;

        if (0 == count($staffUsers) or null === $staffUsers) {
            return $qb->getQuery()->getResult();
        }

        $clause = $qb->expr()->orX();
        $userCount = 1;
        foreach ($staffUsers as $user) {
            $clause->add($qb->expr()->eq('s.employee' , '?'.$userCount));
            $qb->setParameter($userCount, $user);
            $userCount ++;
        }

        return $qb->andWhere($clause)->getQuery()->getResult();

    }

}
