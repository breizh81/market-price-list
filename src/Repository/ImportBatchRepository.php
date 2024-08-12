<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ImportBatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping as ORM;

/**
 * @extends ServiceEntityRepository<ImportBatch>
 *
 * @method ImportBatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportBatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportBatch[]    findAll()
 * @method ImportBatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[ORM\Entity(repositoryClass: ImportBatchRepository::class)]
class ImportBatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportBatch::class);
    }

    /**
     * Saves an ImportBatch entity.
     *
     * @param ImportBatch $importBatch The ImportBatch entity to save.
     */
    public function save(ImportBatch $importBatch): void
    {
        $this->_em->persist($importBatch);
        $this->_em->flush();
    }

    /**
     * Removes an ImportBatch entity.
     *
     * @param ImportBatch $importBatch The ImportBatch entity to remove.
     */
    public function remove(ImportBatch $importBatch): void
    {
        $this->_em->remove($importBatch);
        $this->_em->flush();
    }
}

