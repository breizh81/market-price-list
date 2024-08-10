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

    #[Route('/products', name: 'search_products', methods: ['GET'])]
    public function search(Request $request)
    {
        $keyword = $request->query->get('search');

        $query = $this->productRepository->searchProducts($keyword);

        $pagination = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('product/index.html.twig', ['pagination' => $pagination]);
    }
}
