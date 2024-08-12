<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly PaginatorInterface $paginator
    ) {
    }

    #[Route('/products', name: 'products', methods: ['GET'])]
    public function index(Request $request)
    {
        $searchTerm = $request->query->get('search');

        $query = $searchTerm
            ? $this->productRepository->searchProducts($searchTerm)
            : $this->productRepository->createQueryBuilderForAll();

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('product/index.html.twig', ['paginatedProducts' => $pagination]);
    }
}
