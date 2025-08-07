<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Users;
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
     * @return array<string, mixed>
     */
    public function paginate(int $page = 1, int $limit = self::MAX_ITEMS_PER_PAGE)
    {
        $count = $this->count();

        return [
            'users'        => $this->findBy([], ['id' => 'ASC'], $limit, ($page - 1) * $limit),
            'total_pages'  => ceil($count / self::MAX_ITEMS_PER_PAGE),
            'current_page' => $page,
        ];
    }
}
