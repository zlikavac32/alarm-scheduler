<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler\TestHelper\PHPUnit;

use LogicException;
use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;

class TimeTraceAlarmHandler implements AlarmHandler
{
    /**
     * @var float
     */
    private $start;
    /**
     * @var float
     */
    private $end;

    public function __construct()
    {
        $this->start = microtime(true);
    }

    public function handle(AlarmScheduler $scheduler): void
    {
        if (null !== $this->end) {
            throw new LogicException('Only one call to handle allowed');
        }

        $this->end = microtime(true);
    }

    public function duration(): float
    {
        if (null === $this->start) {
            throw new LogicException('No end information. Did you forget to call handle()?');
        }

        return $this->end - $this->start;
    }

    public function wasHandled(): bool
    {
        return null !== $this->end;
    }
}
