<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Supplier;
use App\Service\File\FileImporter;
use App\Service\File\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly FileImporter $fileImporter,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager
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
            $file = $request->files->get('file');
            $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneBy(['id' => $request->get('supplier')]);

            if (!$file) {
                return new JsonResponse(['status' => 'No file uploaded'], 400);
            }

            $uploadedFilePath = $this->fileUploader->upload($file);

            $this->fileImporter->importFile($uploadedFilePath, $supplier);

            return new JsonResponse(['status' => 'File import has been queued']);
        } catch (\Exception $e) {
            $this->logger->emergency($e->getMessage());

            return new JsonResponse(['status' => 'An error occurred during file import'], 500);
        }
    }
}
