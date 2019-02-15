<?php

declare(strict_types=1);

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\InterruptAlarmHandler;
use Zlikavac32\AlarmScheduler\InterruptException;
use Zlikavac32\AlarmScheduler\NaiveAlarmScheduler;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/common.php';

/*
 * Example that demonstrates how to perform hard interrupt.
 */

pcntl_async_signals(true);

$scheduler = new NaiveAlarmScheduler();

$scheduler->start();

$scheduler->schedule(2, new InterruptAlarmHandler());

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
    } catch (InterruptException $e) {
        printf("Caught %s (%s) at %s\n", get_class($e), $e->getMessage(), nowAsString());
    }
}

/*
Example output:

02m:15s
Caught Zlikavac32\AlarmScheduler\InterruptException (Process interrupted) at 02m:17s
02m:19s
 */
