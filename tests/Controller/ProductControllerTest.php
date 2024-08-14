<?php

namespace App\Tests\Controller;

use App\Controller\ProductController;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Paginator\ProductPaginator;
use App\Service\PhotoGallery\ProductPhotos;
use App\Service\Workflow\ProductCoordinator;
use App\Service\Workflow\ProductMarkingCoordinator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    private $productRepository;
    private $workflowCoordinator;
    private $entityManager;
    private $productMarkingCoordinator;
    private $productPaginator;
    private $productPhotos;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->workflowCoordinator = $this->createMock(ProductCoordinator::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productMarkingCoordinator = $this->createMock(ProductMarkingCoordinator::class);
        $this->productPaginator = $this->createMock(ProductPaginator::class);
        $this->productPhotos = $this->createMock(ProductPhotos::class);
    }

    public function testIndex()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/products');

        $this->productRepository->method('getProductsValidQueryBuilder')->willReturn($this->createMock(QueryBuilder::class));
        $this->productPaginator->method('paginate')->willReturn($this->createMock(PaginationInterface::class));
        $this->productPhotos->method('getProductPhotos')->willReturn([]);

        $controller = new ProductController(
            $this->productRepository,
            $this->workflowCoordinator,
            $this->entityManager,
            $this->productMarkingCoordinator,
            $this->productPaginator,
            $this->productPhotos
        );

        $request = new Request();
        $response = $controller->index($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('No products found', $response->getContent());
    }

    public function testValidateProducts()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/products/validate');

        $this->productRepository->method('getProductsForValidationQueryBuilder')->willReturn($this->createMock(QueryBuilder::class));
        $this->productRepository->method('getProductsForApprovalOrRejectionQueryBuilder')->willReturn($this->createMock(QueryBuilder::class));
        $this->productPaginator->method('paginate')->willReturn($this->createMock(PaginationInterface::class));
        $this->productMarkingCoordinator->method('getProductMarkings')->willReturn([]);

        $controller = new ProductController(
            $this->productRepository,
            $this->workflowCoordinator,
            $this->entityManager,
            $this->productMarkingCoordinator,
            $this->productPaginator,
            $this->productPhotos
        );

        $request = new Request();
        $response = $controller->validateProducts($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('productsToValidate', $response->getContent());
    }

    public function testValidateAction()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_POST, '/products/1/validate', ['action' => 'approve']);

        $product = $this->createMock(Product::class);
        $this->workflowCoordinator->method('approve')->willReturn(null);
        $this->entityManager->method('flush')->willReturn(null);

        $controller = new ProductController(
            $this->productRepository,
            $this->workflowCoordinator,
            $this->entityManager,
            $this->productMarkingCoordinator,
            $this->productPaginator,
            $this->productPhotos
        );

        $request = new Request([], ['action' => 'approve']);
        $response = $controller->validateAction($product, $request);

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertStringContainsString('Product approved successfully.', $client->getResponse()->getContent());
    }
}
