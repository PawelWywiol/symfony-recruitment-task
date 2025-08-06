<?php

namespace App\Repository;

use App\Entity\UsersAddresses;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UsersAddresses>
 */
class UsersAddressesRepository extends ServiceEntityRepository
{
    public const MAX_ITEMS_PER_PAGE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsersAddresses::class);
    }

    /**
     * @return UsersAddresses[] Returns an array of UsersAddresses objects
     */
    public function paginate(int $userId, int $page = 1, int $limit = self::MAX_ITEMS_PER_PAGE): array
    {
        $count = $this->count(['user' => $userId]);

        return [
            'addresses' => $this->findBy(['user' => $userId], ['validFrom' => 'DESC'], $limit, ($page - 1) * $limit),
            'total_pages' => ceil($count / self::MAX_ITEMS_PER_PAGE),
            'current_page' => $page,
        ];
    }
}
