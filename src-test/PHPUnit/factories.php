<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler\TestHelper\PHPUnit;

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;

function createDummyAlarmHandler(): AlarmHandler
{
    return new class implements AlarmHandler
    {
        public function handle(AlarmScheduler $scheduler): void
        {
        }
    };
}
