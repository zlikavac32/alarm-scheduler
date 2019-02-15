<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

class InterruptAlarmHandler implements AlarmHandler
{
    public function handle(AlarmScheduler $scheduler): void
    {
        throw new InterruptException();
    }
}
