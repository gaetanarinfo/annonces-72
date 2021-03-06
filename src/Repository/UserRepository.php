<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Avatar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findEmail($email, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, User::class);
        $this->paginator = $paginator;
    }

    /**
     * @return PaginationInterface
     */
    public function paginateAllVisible(int $page): PaginationInterface
    {
        $query = $this->findVisibleQuery();

        $users = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            12
        );

        $this->hydratePicture($users);
        
        return $users;
    }

    private function findVisibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p');
    }

    private function hydratePicture($user) {
        if (method_exists($user, 'getItems')) {
            $user = $user->getItems();
        }
        $pictures = $this->getEntityManager()->getRepository(Avatar::class)->findForUser($user);
        foreach($user as $users) {
            /** @var $rent Rent */
            if($pictures->containsKey($users->getId())) {
                $users->setPicture($pictures->get($users->getId()));
            }
        }
    }

    /**
     * @return User[]
     */
    public function findLatest(): array
    {
        $user = $this->findVisibleQuery('p')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
        $this->hydratePicture($user);
        return $user;
    }

    /**
     * @return PaginationInterface
     */
    public function paginateAllVisibleModerateur(int $page): PaginationInterface
    {
        $query = $this->findVisibleQuery('p');

        $annonces = $this->paginator->paginate(
            $query
            ->where('p.isActive = :active')
            ->setParameter(':active', 0)
            ->orderBy('p.createDate', 'DESC')
            ->getQuery(),
            $page,
            12
        );

        $this->hydratePicture($annonces);

        return $annonces;
    }

     /**
     * @return Users[]
     */
    public function findCountUsersModerateur()
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
        ->select('count(p.id)')
        ->where('p.isActive = :active')
        ->setParameter(':active', 0)
        ->getQuery()
        ->getSingleScalarResult();

        $this->hydratePicture($qb);

    }

    /**
     * @return Users[]
     */
    public function findCountUsersModerateur2()
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
        ->select('count(p.id)')
        ->where('p.isActive = :active')
        ->setParameter(':active', 1)
        ->getQuery()
        ->getSingleScalarResult();

        $this->hydratePicture($qb);

    }

    /**
     * @return PaginationInterface
     */
    public function paginateAllVisibleModerateur2(int $page): PaginationInterface
    {
        $query = $this->findVisibleQuery('p');

        $annonces = $this->paginator->paginate(
            $query
            ->where('p.isActive = :active')
            ->setParameter(':active', 1)
            ->orderBy('p.createDate', 'DESC')
            ->getQuery(),
            $page,
            12
        );

        $this->hydratePicture($annonces);

        return $annonces;
    }


}
