<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Supplier;
use App\Exception\ProductImportException;
use App\Service\File\ProductFileImporter;
use App\Service\File\ProductFileUploader;
use App\Service\Validator\ProductImportValidator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    public function __construct(
        private readonly ProductFileUploader    $fileUploader,
        private readonly ProductFileImporter    $fileImporter,
        private readonly LoggerInterface        $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductImportValidator $productImportValidator
    ) {
    }

    #[Route('/import', name: 'import', methods: ['GET'])]
    public function index(): Response
    {
        $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();

        return $this->render('import/index.html.twig', [
            'suppliers' => $suppliers,
        ]);
    }

    #[Route('/import/new', name: 'import_new', methods: ['POST', 'GET'])]
    public function import(Request $request): JsonResponse
    {
        try {
            $supplierId = $request->get('supplier');

            if (!is_numeric($supplierId) || intval($supplierId) <= 0) {
                throw new ProductImportException('Invalid supplier ID');
            }

            $supplierId = (int) $supplierId;

            $file = $this->productImportValidator->validateFile($request->files->get('file'));
            $supplier = $this->productImportValidator->validateSupplier($supplierId);

            $uploadedFilePath = $this->fileUploader->upload($file);

            $this->fileImporter->importFile($uploadedFilePath, $supplier);

            return new JsonResponse(['status' => 'File import has been queued']);
        } catch (ProductImportException $e) {
            return new JsonResponse(['status' => $e->getMessage()], 400);
        } catch (Exception $e) {
            $this->logger->emergency($e->getMessage());

            return new JsonResponse(['status' => 'An error occurred during file import'], 500);
        }
    }
}
