<?php
declare(strict_types=1);

namespace App\Entity;

use App\Enum\ProductState;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
class Product implements IdentifiableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 6)]
    #[
        Assert\NotBlank(message: 'products.blank_content'),
        Assert\Length(max: 6, maxMessage: 'products.too_long_code')
    ]
    private string $code;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'products.blank_content')]
    private string $description;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: 'products.blank_content')]
    #[Assert\Positive(message: 'products.negative_price')]
    #[Assert\LessThan(
        value: 1000,
        message: 'products.price_too_high'
    )]
    private float $price;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    #[ORM\Column(type: 'product_state', length: 10)]
    private ProductState $state;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $marking = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getState(): ProductState
    {
        return $this->state;
    }

    public function setState(ProductState $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getMarking(): ?string
    {
        return $this->marking;
    }

    public function setMarking(?string $marking): static
    {
        $this->marking = $marking;

        return $this;
    }
}
