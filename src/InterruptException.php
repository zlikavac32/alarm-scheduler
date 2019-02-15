<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

use Exception;
use Throwable;

class InterruptException extends Exception
{
    public function __construct(string $message = "Process interrupted", Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
