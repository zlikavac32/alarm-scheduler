<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

use Throwable;

class IgnoreThrowableHandler implements ThrowableHandler {

    public function handle(Throwable $throwable): void
    {
        // just ignore
    }
}
