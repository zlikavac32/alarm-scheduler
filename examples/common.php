<?php

declare(strict_types=1);

function nowAsString(): string
{
    return (new DateTime())->format('i\\m:s\\s');
}

function printNow(): void
{
    echo nowAsString(), "\n";
}

/**
 * Sleep is interrupted by the signal so we must sleep again if there was any remaining time left to sleep.
 */
function sleepWithoutInterrupt(int $sleep): void
{
    // this is for demonstrative purposes, see http://man7.org/linux/man-pages/man3/sleep.3.html#notes
    $now = microtime(true);
    $end = $now + $sleep;

    do {
        sleep($sleep);
        $now = microtime(true);

        $sleep = max(1, (int)floor($end - $now));
    } while ($now < $end);
}
