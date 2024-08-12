<?php
declare(strict_types=1);

namespace App\Doctrine\Type;

use App\Enum\ProductState;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class ProductStateType extends StringType
{
    const PRODUCT_STATE = 'product_state';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "VARCHAR(10)";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ProductState
    {
        if ($value === null) {
            return null;
        }
        return ProductState::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value ? $value->getValue() : null;
    }

    public function getName(): string
    {
        return self::PRODUCT_STATE;
    }
}

