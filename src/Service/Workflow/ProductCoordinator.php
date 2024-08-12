<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Entity\Product;
use App\Enum\ProductState;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\StateMachine;

class ProductCoordinator
{
    public function __construct(private readonly StateMachine $productValidationStateMachine)
    {
    }

    public function transitionToValidating(Product $product): void
    {
        try {
            $this->validate($product);
            $product->setState(ProductState::VALIDATING);
        } catch (LogicException $e) {
            throw new LogicException($e->getMessage());
        }
    }

    public function validate(Product $product): void
    {
        if ($this->productValidationStateMachine->can($product, 'validate')) {
            $this->productValidationStateMachine->apply($product, 'validate');
            $product->setState(ProductState::VALIDATING);
        } else {
            throw new LogicException('Cannot validate product in its current state.');
        }
    }

    public function approve(Product $product): void
    {
        if ($this->productValidationStateMachine->can($product, 'approve')) {
            $this->productValidationStateMachine->apply($product, 'approve');
            $product->setState(ProductState::VALID);
        } else {
            throw new LogicException('Cannot approve product in its current state.');
        }
    }

    public function reject(Product $product): void
    {
        if ($this->productValidationStateMachine->can($product, 'reject')) {
            $this->productValidationStateMachine->apply($product, 'reject');
            $product->setState(ProductState::INVALID);
        } else {
            throw new LogicException('Cannot reject product in its current state.');
        }
    }
}

