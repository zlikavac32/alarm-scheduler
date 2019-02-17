<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

use Throwable;

/**
 * Catches any throwable and calls custom handler. Any exception from the throwable handler is silently ignored.
 */
class CatchThrowableAlarmHandler implements AlarmHandler
{

    /**
     * @var AlarmHandler
     */
    private $alarmHandler;
    /**
     * @var ThrowableHandler
     */
    private $throwableHandler;

    public function __construct(AlarmHandler $alarmHandler, ThrowableHandler $throwableHandler)
    {
        $this->alarmHandler = $alarmHandler;
        $this->throwableHandler = $throwableHandler;
    }

    public function handle(AlarmScheduler $scheduler): void
    {
        try {
            $this->alarmHandler->handle($scheduler);
        } catch (Throwable $e) {
            try {
                $this->throwableHandler->handle($e);
            } catch (Throwable $e) {
                // ignore
            }
        }
    }
}
