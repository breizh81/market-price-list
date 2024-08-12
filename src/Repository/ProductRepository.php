<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Enum\ProductState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($product);
        $entityManager->flush();
    }

    public function searchProducts(string $keyword): Query
    {
        $keyword = strtolower($keyword);

        return $this->createQueryBuilder('p')
            ->where('LOWER(p.description) LIKE :keyword')
            ->orWhere('LOWER(p.code) LIKE :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->getQuery();
    }

    public function getProductsForValidationQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where('p.state = :state')
            ->setParameter('state', ProductState::NEW)
            ->orderBy('p.id', 'ASC');
    }

    public function createQueryBuilderForAll(): QueryBuilder
    {
        return $this->createQueryBuilder('p');
    }
}
