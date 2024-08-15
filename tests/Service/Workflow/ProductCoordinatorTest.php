<?php

declare(strict_types=1);

namespace App\Tests\Service\Workflow;

use App\Entity\Product;
use App\Enum\ProductState;
use App\Service\Workflow\ProductCoordinator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\StateMachine;

class ProductCoordinatorTest extends TestCase
{
    private StateMachine $stateMachine;
    private ProductCoordinator $productCoordinator;
    private Product $product;

    protected function setUp(): void
    {
        $this->stateMachine = $this->createMock(StateMachine::class);
        $this->productCoordinator = new ProductCoordinator($this->stateMachine);
        $this->product = $this->createMock(Product::class);
    }

    public function testTransitionToValidating(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'validate')->willReturn(true);
        $this->stateMachine->expects($this->once())->method('apply')->with($this->product, 'validate');

        $this->product->expects($this->once())->method('setState')->with(ProductState::VALIDATING);

        $this->productCoordinator->transitionToValidating($this->product);
    }

    public function testTransitionToValidatingWithException(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'validate')->willReturn(false);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot validate product in its current state.');

        $this->productCoordinator->transitionToValidating($this->product);
    }

    public function testValidate(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'validate')->willReturn(true);
        $this->stateMachine->expects($this->once())->method('apply')->with($this->product, 'validate');

        $this->product->expects($this->once())->method('setState')->with(ProductState::VALIDATING);

        $this->productCoordinator->validate($this->product);
    }

    public function testValidateWithException(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'validate')->willReturn(false);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot validate product in its current state.');

        $this->productCoordinator->validate($this->product);
    }

    public function testApprove(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'approve')->willReturn(true);
        $this->stateMachine->expects($this->once())->method('apply')->with($this->product, 'approve');

        $this->product->expects($this->once())->method('setState')->with(ProductState::VALID);

        $this->productCoordinator->approve($this->product);
    }

    public function testApproveWithException(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'approve')->willReturn(false);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot approve product in its current state.');

        $this->productCoordinator->approve($this->product);
    }

    public function testReject(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'reject')->willReturn(true);
        $this->stateMachine->expects($this->once())->method('apply')->with($this->product, 'reject');

        $this->product->expects($this->once())->method('setState')->with(ProductState::INVALID);

        $this->productCoordinator->reject($this->product);
    }

    public function testRejectWithException(): void
    {
        $this->stateMachine->method('can')->with($this->product, 'reject')->willReturn(false);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot reject product in its current state.');

        $this->productCoordinator->reject($this->product);
    }
}
