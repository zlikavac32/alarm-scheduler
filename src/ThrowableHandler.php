<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

use Throwable;

/**
 * Called from {@see CatchThrowableAlarmHandler} when throwable is caught.
 */
interface ThrowableHandler {

    public function handle(Throwable $throwable): void;
}
