<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use Symfony\Component\Workflow\StateMachine;

class ProductMarkingCoordinator
{
    public function __construct(private readonly StateMachine $productValidationStateMachine)
    {
    }
    public function getProductMarkings(array $products): array
    {
        $markings = [];
        foreach ($products as $product) {
            $marking = $this->productValidationStateMachine->getMarking($product);
            $markings[$product->getId()] = implode(', ', $marking->getPlaces());
        }

        return $markings;
    }
}
