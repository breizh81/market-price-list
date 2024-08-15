<?php

declare(strict_types=1);

namespace App\Service\Paginator;

use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ProductPaginator
{
    public function __construct(private readonly PaginatorInterface $paginator)
    {
    }

    public function paginate(QueryBuilder $queryBuilder, int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate($queryBuilder, $page, $limit);
    }
}
