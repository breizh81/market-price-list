<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Entity\Supplier;
use App\Enum\ProductState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProductControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private int $productId;

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $supplier = (new Supplier())->setName('test');
        $this->entityManager->persist($supplier);

        $product = (new Product())
            ->setDescription('Test Product')
            ->setPrice(1.0)
            ->setCode('A123')
            ->setState(ProductState::NEW)
            ->setSupplier($supplier);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->productId = $product->getId();
    }

    protected function tearDown(): void
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        foreach ($products as $product) {
            $this->entityManager->remove($product);
        }
        $this->entityManager->flush();

        $this->entityManager->close();

        parent::tearDown();
    }

    public function testValidateAction(): void
    {
        $this->client->request(Request::METHOD_POST, "/products/{$this->productId}/validate", ['action' => 'valid']);

        $this->assertResponseRedirects('/products/validate');

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.flash-info');
    }

    public function testIndex(): void
    {
        $this->client->request(Request::METHOD_GET, '/products', ['page' => 1]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.table');
    }
}
