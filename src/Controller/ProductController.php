<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Paginator\ProductPaginator;
use App\Service\Workflow\ProductCoordinator;
use App\Service\Workflow\ProductMarkingCoordinator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductCoordinator $workflowCoordinator,
        private readonly EntityManagerInterface $entityManager,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly ProductMarkingCoordinator $productMarkingCoordinator,
        private readonly ProductPaginator $productPaginator
    ) {
    }

    #[Route('/products', name: 'products', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchTerm = $request->query->get('search');

        $query = $searchTerm
            ? $this->productRepository->searchProducts($searchTerm)
            : $this->productRepository->createQueryBuilderForAll();

        $pagination = $this->productPaginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('product/index.html.twig', ['paginatedProducts' => $pagination]);
    }

    #[Route('/products/validate', name: 'product_validate')]
    public function validateProducts(Request $request): Response
    {
        $queryBuilder = $this->productRepository->getProductsForValidationQueryBuilder();
        $pagination = $this->productPaginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('product/validate.html.twig', [
            'pagination' => $pagination,
            'markings' => $this->productMarkingCoordinator->getProductMarkings($pagination->getItems()),
        ]);
    }

    #[Route('/products/{id}/validate', name: 'product_validate_action', methods: ['POST'])]
    public function validateAction(Product $product, Request $request): Response
    {
        $token = $request->request->get('_csrf_token');

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('validate_' . $request->get('id'), $token))) {
            $this->addFlash('error', 'Invalid CSRF token');
            throw new InvalidCsrfTokenException('Invalid CSRF token');
        }

        $action = $request->request->get('action');

        try {
            switch ($action) {
                case 'approve':
                    $this->workflowCoordinator->approve($product);
                    $this->addFlash('success', 'Product approved successfully.');
                    break;
                case 'reject':
                    $this->workflowCoordinator->reject($product);
                    $this->addFlash('error', 'Product rejected.');
                    break;
                default:
                    $this->workflowCoordinator->validate($product);
                    $this->addFlash('info', 'Product validation initiated.');
                    break;
            }

            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->redirectToRoute('product_validate');
    }
}
