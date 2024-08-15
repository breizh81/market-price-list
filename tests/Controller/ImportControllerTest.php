<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Supplier;
use App\Exception\ProductImportException;
use App\Repository\SupplierRepository;
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
    private ProductFileUploader $fileUploader;
    private ProductFileImporter $fileImporter;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private ProductImportValidator $productImportValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileUploader = $this->createMock(ProductFileUploader::class);
        $this->fileImporter = $this->createMock(ProductFileImporter::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productImportValidator = $this->createMock(ProductImportValidator::class);

        // Mocking the SupplierRepository
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $this->entityManager
            ->method('getRepository')
            ->willReturn($supplierRepository);
        $supplierRepository
            ->method('findAll')
            ->willReturn([new Supplier()]);
        $supplierRepository
            ->method('find')
            ->willReturn(new Supplier());
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/import');

        $this->assertResponseIsSuccessful();

        // Check if the select list contains the supplier options
        $crawler = $client->getCrawler();
        $this->assertSelectorTextContains('#selectList', 'Fruit supplier');
        $this->assertSelectorTextContains('#selectList', 'Dairy supplier');
        $this->assertSelectorTextContains('#selectList', 'Meat supplier');
        $this->assertSelectorTextContains('#selectList', 'Vegetable supplier');
        $this->assertSelectorTextContains('#selectList', 'Beverage supplier');
    }

    public function testImportInvalidSupplier(): void
    {
        $client = static::createClient();

        $tempFile = tmpfile();
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        $csvContent = 'kiwi,kiwi,1.02';
        file_put_contents($tempFilePath, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test.csv',
            'text/csv',
            null,
            true
        );

        $this->productImportValidator
            ->method('validateFile')
            ->willReturn($uploadedFile);
        $this->productImportValidator
            ->method('validateSupplier')
            ->willThrowException(new ProductImportException('Invalid supplier ID'));

        $client->request(
            Request::METHOD_POST,
            '/import/new',
            ['supplier' => 'invalid'],
            ['file' => $uploadedFile],
            ['CONTENT_TYPE' => 'multipart/form-data']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Invalid supplier ID']),
            $client->getResponse()->getContent()
        );

        fclose($tempFile);
    }
}
