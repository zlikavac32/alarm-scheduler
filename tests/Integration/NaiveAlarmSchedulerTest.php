<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler\Tests\Integration;

use LogicException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\TestCase;
use Zlikavac32\AlarmScheduler\InterruptAlarmHandler;
use Zlikavac32\AlarmScheduler\InterruptException;
use Zlikavac32\AlarmScheduler\NaiveAlarmScheduler;
use Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\Constraint\IsCurrentAlarmSignalHandler;
use Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\Constraint\WasAlarmHandlerCalledAfterNSeconds;
use Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\Constraint\WasAlarmHandlerCalledAtAll;
use Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\TimeTraceAlarmHandler;
use function Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\createDummyAlarmHandler;
use function Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\sleepWithoutInterrupt;

class NaiveAlarmSchedulerTest extends TestCase
{

    private static $previousAsyncSignals;

    private static $previousSignalHandler;

    public static function setUpBeforeClass()
    {
        self::$previousAsyncSignals = pcntl_async_signals(true);
        self::$previousSignalHandler = pcntl_signal_get_handler(SIGALRM);
    }

    public static function tearDownAfterClass()
    {
        pcntl_async_signals(self::$previousAsyncSignals);
        pcntl_signal(SIGALRM, self::$previousSignalHandler);
    }

    protected function setUp()
    {
        pcntl_alarm(0);
        pcntl_signal(SIGALRM, SIG_DFL);
    }

    protected function tearDown()
    {
        pcntl_alarm(0);
        pcntl_signal(SIGALRM, SIG_DFL);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Scheduler already started
     * @test
     */
    public function started_scheduler_can_not_be_started_again(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        try {
            $scheduler->start();
        } catch (LogicException $e) {
            $this->fail(sprintf('Unexpected exception %s (%s) caught', get_class($e), $e->getMessage()));
        }

        $scheduler->start();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Scheduler not started. Did you forget to call start()?
     * @test
     */
    public function scheduler_must_be_started_before_schedule(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->schedule(1, createDummyAlarmHandler());
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Scheduler not started. Did you forget to call start()?
     * @test
     */
    public function scheduler_must_be_started_before_remove(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->remove(createDummyAlarmHandler());
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Scheduler not started. Did you forget to call start()?
     * @test
     */
    public function scheduler_must_be_started_before_finish(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->finish();
    }

    /**
     * @test
     */
    public function it_should_restore_alarm_signal_handler(): void
    {
        $signalHandler = function (): void {

        };

        pcntl_signal(SIGALRM, $signalHandler);

        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        self::assertThat(
            $signalHandler,
            new LogicalNot(
                new IsCurrentAlarmSignalHandler()
            )
        );

        $scheduler->finish();

        self::assertThat($signalHandler, new IsCurrentAlarmSignalHandler());
    }

    /**
     * @test
     */
    public function it_should_deliver_scheduled_item(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $handler = new TimeTraceAlarmHandler();

        $scheduler->schedule(2, $handler);

        sleep(3);

        $scheduler->finish();

        self::assertThat($handler, new WasAlarmHandlerCalledAfterNSeconds(2));
    }

    /**
     * @test
     */
    public function it_should_deliver_multiple_scheduled_items(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $firstHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(2, $firstHandler);

        $secondHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(5, $secondHandler);

        $thirdHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(7, $thirdHandler);

        sleepWithoutInterrupt(8);

        $scheduler->finish();

        self::assertThat($firstHandler, new WasAlarmHandlerCalledAfterNSeconds(2));
        self::assertThat($secondHandler, new WasAlarmHandlerCalledAfterNSeconds(5));
        self::assertThat($thirdHandler, new WasAlarmHandlerCalledAfterNSeconds(7));
    }

    /**
     * @test
     */
    public function it_should_deliver_multiple_scheduled_items_when_scheduled_in_different_order(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $firstHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(7, $firstHandler);

        $secondHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(2, $secondHandler);

        $thirdHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(5, $thirdHandler);

        sleepWithoutInterrupt(8);

        $scheduler->finish();

        self::assertThat($firstHandler, new WasAlarmHandlerCalledAfterNSeconds(7));
        self::assertThat($secondHandler, new WasAlarmHandlerCalledAfterNSeconds(2));
        self::assertThat($thirdHandler, new WasAlarmHandlerCalledAfterNSeconds(5));
    }

    /**
     * @test
     */
    public function it_should_deliver_scheduled_items_with_same_timeout(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $firstHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(2, $firstHandler);

        $secondHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(2, $secondHandler);

        sleepWithoutInterrupt(4);

        $scheduler->finish();

        self::assertThat($firstHandler, new WasAlarmHandlerCalledAfterNSeconds(2));
        self::assertThat($secondHandler, new WasAlarmHandlerCalledAfterNSeconds(2));
    }

    /**
     * @test
     */
    public function it_should_not_call_removed_handler(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $handler = new TimeTraceAlarmHandler();

        $scheduler->schedule(2, $handler);

        sleepWithoutInterrupt(1);

        $scheduler->remove($handler);

        sleepWithoutInterrupt(2);

        $scheduler->finish();

        self::assertThat(
            $handler,
            new LogicalNot(
                new WasAlarmHandlerCalledAtAll()
            )
        );
    }

    /**
     * @test
     */
    public function it_should_reschedule_next_handler_after_remove(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $firstHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(2, $firstHandler);

        $secondHandler = new TimeTraceAlarmHandler();

        $scheduler->schedule(4, $secondHandler);

        sleepWithoutInterrupt(1);

        $scheduler->remove($firstHandler);

        sleepWithoutInterrupt(5);

        $scheduler->finish();

        self::assertThat(
            $firstHandler,
            new LogicalNot(
                new WasAlarmHandlerCalledAtAll()
            )
        );
        self::assertThat($secondHandler, new WasAlarmHandlerCalledAfterNSeconds(4));
    }

    /**
     * @expectedException \Zlikavac32\AlarmScheduler\InterruptException
     * @test
     */
    public function it_should_forward_interrupt_exception(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $scheduler->schedule(2, new InterruptAlarmHandler());

        try {
            sleepWithoutInterrupt(3);
        } finally {
            $scheduler->finish();
        }
    }

    /**
     * @test
     */
    public function it_should_continue_excecution_after_interrupt_exception(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        $scheduler->start();

        $scheduler->schedule(2, new InterruptAlarmHandler());

        $handler = new TimeTraceAlarmHandler();

        $scheduler->schedule(3, $handler);

        try {
            sleepWithoutInterrupt(3);

            $this->fail(sprintf('Expected %s to be thrown', InterruptException::class));
        } catch (InterruptException $e) {
            // ignore
        }

        sleepWithoutInterrupt(1);

        $scheduler->finish();

        self::assertThat($handler, new WasAlarmHandlerCalledAfterNSeconds(3));
    }

    /**
     * @test
     */
    public function it_should_clear_any_pending_alarms_when_starting(): void
    {
        $scheduler = new NaiveAlarmScheduler();

        pcntl_alarm(2);

        $scheduler->start();

        pcntl_signal(SIGALRM, function () {
            $this->fail('Expected alarm to be cleared');
        });

        $scheduler->finish();

        $this->assertTrue(true);
    }
}
