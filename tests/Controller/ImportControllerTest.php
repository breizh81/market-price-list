<?php

namespace App\Tests\Controller;

use App\Controller\ImportController;
use App\Entity\Supplier;
use App\Exception\ProductImportException;
use App\Service\File\ProductFileImporter;
use App\Service\File\ProductFileUploader;
use App\Service\Validator\ProductImportValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImportControllerTest extends WebTestCase
{
    private $fileUploader;
    private $fileImporter;
    private $logger;
    private $entityManager;
    private $productImportValidator;

    protected function setUp(): void
    {
        $this->fileUploader = $this->createMock(ProductFileUploader::class);
        $this->fileImporter = $this->createMock(ProductFileImporter::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productImportValidator = $this->createMock(ProductImportValidator::class);
    }

    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/import');

        $supplierRepository = $this->createMock(SupplierRepository::class);
        $this->entityManager->method('getRepository')->willReturn($supplierRepository);
        $supplierRepository->method('findAll')->willReturn([]);

        $controller = new ImportController(
            $this->fileUploader,
            $this->fileImporter,
            $this->logger,
            $this->entityManager,
            $this->productImportValidator
        );

        $response = $controller->index();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('suppliers', $response->getContent());
    }

    public function testImportSuccess()
    {
        $client = static::createClient();
        $client->request('POST', '/import/new', [], ['file' => new UploadedFile('/path/to/file', 'file.csv')], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->productImportValidator->method('validateFile')->willReturn(new UploadedFile('/path/to/file', 'file.csv'));
        $this->productImportValidator->method('validateSupplier')->willReturn(new Supplier());
        $this->fileUploader->method('upload')->willReturn('/uploaded/path/to/file.csv');
        $this->fileImporter->method('importFile')->willReturn(null);

        $controller = new ImportController(
            $this->fileUploader,
            $this->fileImporter,
            $this->logger,
            $this->entityManager,
            $this->productImportValidator
        );

        $request = new Request([], ['supplier' => 1], [], ['file' => new UploadedFile('/path/to/file', 'file.csv')]);
        $response = $controller->import($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('File import has been queued', $response->getContent());
    }

    public function testImportInvalidSupplier()
    {
        $client = static::createClient();
        $client->request('POST', '/import/new', [], ['file' => new UploadedFile('/path/to/file', 'file.csv')], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->productImportValidator->method('validateFile')->willReturn(new UploadedFile('/path/to/file', 'file.csv'));
        $this->productImportValidator->method('validateSupplier')->willThrowException(new ProductImportException('Invalid supplier ID'));

        $controller = new ImportController(
            $this->fileUploader,
            $this->fileImporter,
            $this->logger,
            $this->entityManager,
            $this->productImportValidator
        );

        $request = new Request([], ['supplier' => 'invalid'], [], ['file' => new UploadedFile('/path/to/file', 'file.csv')]);
        $response = $controller->import($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('Invalid supplier ID', $response->getContent());
    }

    public function testImportException()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/import/new', [], ['file' => new UploadedFile('/path/to/file', 'file.csv')], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->productImportValidator->method('validateFile')->willReturn(new UploadedFile('/path/to/file', 'file.csv'));
        $this->productImportValidator->method('validateSupplier')->willReturn(new Supplier());
        $this->fileUploader->method('upload')->willReturn('/uploaded/path/to/file.csv');
        $this->fileImporter->method('importFile')->willThrowException(new \Exception('Import error'));

        $controller = new ImportController(
            $this->fileUploader,
            $this->fileImporter,
            $this->logger,
            $this->entityManager,
            $this->productImportValidator
        );

        $request = new Request([], ['supplier' => 1], [], ['file' => new UploadedFile('/path/to/file', 'file.csv')]);
        $response = $controller->import($request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('An error occurred during file import', $response->getContent());
    }
}
