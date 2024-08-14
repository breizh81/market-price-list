<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Mailer\Exception\TransportException;

class UploadEmailException extends TransportException
{
}
