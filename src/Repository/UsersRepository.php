<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\UsersAddresses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository
{
    public const MAX_ITEMS_PER_PAGE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * @return Users[] Returns an array of Users objects
     */
    public function paginate(int $page = 1, int $limit = self::MAX_ITEMS_PER_PAGE): array
    {
        $count = $this->count();

        return [
            'users' => $this->findBy([], ['id' => 'ASC'], $limit, ($page - 1) * $limit),
            'total_pages' => ceil($count / self::MAX_ITEMS_PER_PAGE),
            'current_page' => $page,
        ];
    }

    public function getUserAddresses(int $userId, int $page = 1): array
    {
        $user = $this->find($userId);
        if (!$user) {
            return [];
        }

        return $this->getEntityManager()
            ->getRepository(UsersAddresses::class)
            ->paginate($userId, $page);
    }
}
