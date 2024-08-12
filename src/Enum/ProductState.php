<?php
declare(strict_types=1);

namespace App\Enum;

enum ProductState: string
{
    case NEW = 'new';
    case VALIDATING = 'validating';
    case VALID = 'valid';
    case INVALID = 'invalid';

    public function getValue(): string
    {
        return $this->value;
    }
}
