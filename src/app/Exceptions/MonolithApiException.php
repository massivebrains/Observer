<?php declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class MonolithApiException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct(sprintf('Monolith Api returned http status code %s', $message));
    }
}
