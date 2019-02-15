<?php

declare(strict_types=1);

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\NaiveAlarmScheduler;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/common.php';

/*
 * Example that demonstrates how to schedule multiple handlers.
 */

pcntl_async_signals(true);

$scheduler = new NaiveAlarmScheduler();

$scheduler->start();

$scheduler->schedule(5, new class implements AlarmHandler
{

    public function handle(AlarmScheduler $scheduler): void
    {
        printNow();
    }
});

$scheduler->schedule(2, new class implements AlarmHandler
{

    public function handle(AlarmScheduler $scheduler): void
    {
        printNow();
    }
});

$scheduler->schedule(10, new class implements AlarmHandler
{

    public function handle(AlarmScheduler $scheduler): void
    {
        printNow();
    }
});

printNow();

sleepWithoutInterrupt(14);

/*
Example output:
00m:00s
00m:02s
00m:05s
00m:10s
 */
