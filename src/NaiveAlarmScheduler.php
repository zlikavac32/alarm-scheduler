<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

use Ds\Vector;
use LogicException;

/**
 * Naive scheduler implementation that uses list with sort to maintain order of timeouts.
 */
class NaiveAlarmScheduler implements AlarmScheduler
{
    /**
     * @var Vector|ScheduleItem[]
     */
    private $scheduleItems;

    private $nextScheduleAt = 1e15;

    private $started = false;

    private $oldSignalHandler;

    public function __construct()
    {
        $this->scheduleItems = new Vector();
    }

    /**
     * Prepares environment for scheduler
     */
    public function start(): void
    {
        if ($this->started) {
            throw new LogicException('Scheduler already started');
        }

        $this->oldSignalHandler = pcntl_signal_get_handler(SIGALRM);

        pcntl_alarm(0);

        pcntl_signal(
            SIGALRM,
            function () {
                $this->handleSignal();
            }
        );

        pcntl_sigprocmask(SIG_UNBLOCK, [SIGALRM]);

        $this->started = true;
    }

    private function handleSignal(): void
    {
        $exceptionToRethrow = null;

        while (!$this->scheduleItems->isEmpty()) {
            $now = microtime(true);

            $scheduleItem = $this->scheduleItems[0];

            if ($now < $scheduleItem->timeStamp()) {
                break;
            }

            $this->scheduleItems->shift();

            try {
                $scheduleItem->handler()
                    ->handle($this);
            } catch (InterruptException $e) {
                $exceptionToRethrow = $e;
            }
        }

        $this->scheduleNext();

        if ($exceptionToRethrow) {
            throw $exceptionToRethrow;
        }
    }

    public function schedule(int $timeout, AlarmHandler $handler): void
    {
        $this->assertSchedulerStarted();

        $this->runInSignalIsolation(
            function (int $timeout, AlarmHandler $handler): void {
                $this->unsafeSchedule($timeout, $handler);
            },
            $timeout,
            $handler
        );
    }

    private function unsafeSchedule(int $timeout, AlarmHandler $handler): void
    {
        $now = microtime(true);

        $this->scheduleItems->push(new ScheduleItem($now + $timeout, $handler));

        $this->scheduleItems->sort(
            function (ScheduleItem $first, ScheduleItem $second): int {
                return $first->timeStamp() <=> $second->timeStamp();
            }
        );

        if ($now + $timeout < $this->nextScheduleAt) {
            pcntl_alarm($timeout);
            $this->nextScheduleAt = $now + $timeout;
        }
    }

    public function remove(AlarmHandler $handler): void
    {
        $this->assertSchedulerStarted();

        $this->runInSignalIsolation(
            function (AlarmHandler $handler): void {
                $this->unsafeRemove($handler);
            },
            $handler
        );
    }

    private function unsafeRemove(AlarmHandler $handler): void
    {
        if ($this->scheduleItems->isEmpty()) {
            return;
        }

        $needReschedule = $this->scheduleItems[0]->handler() === $handler;

        // it's unsafe to modify in foreach
        $this->scheduleItems = $this->scheduleItems->filter(function (ScheduleItem $scheduleItem) use ($handler) {
            return $scheduleItem->handler() !== $handler;
        });

        if ($needReschedule) {
            $this->scheduleNext();
        }
    }

    public function finish(): void
    {
        $this->assertSchedulerStarted();

        pcntl_alarm(0);
        $this->scheduleItems->clear();
        $this->nextScheduleAt = 1e15;

        pcntl_signal(SIGALRM, $this->oldSignalHandler);
    }

    private function assertSchedulerStarted(): void
    {
        if ($this->started) {
            return;
        }

        throw new LogicException('Scheduler not started. Did you forget to call start()?');
    }

    private function scheduleNext(): void
    {
        if ($this->scheduleItems->isEmpty()) {
            $this->nextScheduleAt = 1e25;

            return;
        }

        $scheduleItem = $this->scheduleItems[0];

        pcntl_alarm(
            max(
                1,
                (int)($scheduleItem->timeStamp() - microtime(true))
            )
        );

        $this->nextScheduleAt = $scheduleItem->timeStamp();
    }

    private function runInSignalIsolation(callable $callable, ...$args): void
    {
        pcntl_sigprocmask(SIG_BLOCK, [SIGALRM]);

        try {
            $callable(...$args);
        } finally {
            pcntl_sigprocmask(SIG_UNBLOCK, [SIGALRM]);
        }
    }
}

/**
 * Class private to NaiveAlarmSchedule, used to avoid arrays
 *
 * @internal
 */
class ScheduleItem
{

    /**
     * @var float
     */
    private $timeStamp;
    /**
     * @var AlarmHandler
     */
    private $handler;

    public function __construct(float $timeStamp, AlarmHandler $handler)
    {
        $this->timeStamp = $timeStamp;
        $this->handler = $handler;
    }

    public function timeStamp(): float
    {
        return $this->timeStamp;
    }

    public function handler(): AlarmHandler
    {
        return $this->handler;
    }
}
