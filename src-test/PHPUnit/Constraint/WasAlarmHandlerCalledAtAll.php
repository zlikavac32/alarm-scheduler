<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\TimeTraceAlarmHandler;

class WasAlarmHandlerCalledAtAll extends Constraint
{
    protected function matches($other): bool
    {
        assert($other instanceof TimeTraceAlarmHandler);

        return $other->wasHandled();
    }

    protected function failureDescription($other): string
    {
        assert($other instanceof TimeTraceAlarmHandler);

        return 'handler was called';
    }

    public function toString(): string
    {
        return 'was called';
    }
}
