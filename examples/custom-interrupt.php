<?php

declare(strict_types=1);

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\NaiveAlarmScheduler;

if (!extension_loaded('posix')) {
    die('Extension posix missing');
}

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/common.php';

/*
 * Example that demonstrates how to throw custom exception that should perform hard interrupt.
 */

pcntl_async_signals(true);

pcntl_signal(
    SIGUSR1,
    function (): void {
        throw new RuntimeException('Our custom exception');
    }
);

$scheduler = new NaiveAlarmScheduler();

$scheduler->start();

$scheduler->schedule(
    2,
    new class implements AlarmHandler
    {

        public function handle(AlarmScheduler $scheduler): void
        {
            posix_kill(getmypid(), SIGUSR1);
        }
    }
);

$scheduler->schedule(
    4,
    new class implements AlarmHandler
    {

        public function handle(AlarmScheduler $scheduler): void
        {
            printNow();
        }
    }
);

printNow();

pcntl_alarm(2);

while (true) {
    try {
        sleepWithoutInterrupt(5);

        break;
    } catch (RuntimeException $e) {
        printf("Caught %s (%s) at %s\n", get_class($e), $e->getMessage(), nowAsString());
    }
}

/*
Example output:

00m:08s
Caught RuntimeException (Our custom exception) at 00m:10s
00m:12s
 */
