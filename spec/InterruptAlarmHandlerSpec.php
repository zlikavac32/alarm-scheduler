<?php

declare(strict_types=1);

namespace spec\Zlikavac32\AlarmScheduler;

use PhpSpec\ObjectBehavior;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\InterruptAlarmHandler;
use Zlikavac32\AlarmScheduler\InterruptException;

class InterruptAlarmHandlerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(InterruptAlarmHandler::class);
    }

    public function it_should_throw_interrupt_exception(AlarmScheduler $scheduler): void
    {
        $this->shouldThrow(new InterruptException())
            ->duringHandle($scheduler);
    }
}
