<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\File\FileImporter;
use App\Service\File\FileUploader;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/import')]
class ImportController extends AbstractController
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly FileImporter $fileImporter,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/new', name: 'import_new', methods: ['POST'])]
    public function import(Request $request): JsonResponse
    {
        try {
            $file = $request->files->get('file');

            if (!$file) {
                return new JsonResponse(['status' => 'No file uploaded'], 400);
            }

            $uploadedFilePath = $this->fileUploader->upload($file);

            $this->fileImporter->importFile($uploadedFilePath);

            return new JsonResponse(['status' => 'File import has been queued']);
        } catch (\Exception $e) {
            $this->logger->emergency($e->getMessage());

            return new JsonResponse(['status' => 'An error occurred during file import'], 500);
        }
    }
}
