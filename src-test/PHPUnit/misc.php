<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler\TestHelper\PHPUnit;

// works on linux (http://man7.org/linux/man-pages/man3/sleep.3.html#notes)
function sleepWithoutInterrupt(int $sleep): void
{
    $now = microtime(true);
    $end = $now + $sleep;

    do {
        sleep($sleep);
        $now = microtime(true);

        $sleep = max(1, (int)floor($end - $now));
    } while ($now < $end);
}
