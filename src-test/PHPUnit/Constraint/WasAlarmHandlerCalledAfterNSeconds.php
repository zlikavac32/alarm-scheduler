<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\TimeTraceAlarmHandler;

class WasAlarmHandlerCalledAfterNSeconds extends Constraint
{
    /**
     * @var int
     */
    private $period;

    public function __construct(int $period)
    {
        parent::__construct();

        $this->period = $period;
    }

    protected function matches($other): bool
    {
        assert($other instanceof TimeTraceAlarmHandler);

        return $other->duration() > $this->period;
    }

    protected function failureDescription($other): string
    {
        assert($other instanceof TimeTraceAlarmHandler);

        $str = sprintf('handler was called after %d seconds', $this->period);

        switch (true) {
            case !$other->wasHandled():
                $str .= ' (handle() was never called)';
                break;
            default:
                $str .= sprintf(' (called after %.6lf seconds', $other->duration());
        }

        return $str;
    }

    public function toString(): string
    {
        return sprintf('was called after %d seconds', $this->period);
    }
}
