<?php

declare(strict_types=1);

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\NaiveAlarmScheduler;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/common.php';

/*
 * Example that demonstrates simplest usage of alarm scheduler.
 */

pcntl_async_signals(true);

$scheduler = new NaiveAlarmScheduler();

$scheduler->start();

$handler = new class implements AlarmHandler
{

    public function handle(AlarmScheduler $scheduler): void
    {
        printNow();
    }
};

$scheduler->schedule(5, $handler); // to run $handler in 5 seconds

printNow();

sleepWithoutInterrupt(7);

printNow();

/*
Example output:

04m:34s
04m:39s
 */
