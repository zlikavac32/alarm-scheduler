<?php

declare(strict_types=1);

namespace spec\Zlikavac32\AlarmScheduler;

use Exception;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\CatchThrowableAlarmHandler;
use Zlikavac32\AlarmScheduler\ThrowableHandler;

class CatchThrowableAlarmHandlerSpec extends ObjectBehavior
{
    public function let(AlarmHandler $alarmHandler, ThrowableHandler $throwableHandler): void
    {
        $this->beConstructedWith($alarmHandler, $throwableHandler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CatchThrowableAlarmHandler::class);
    }

    public function it_should_not_handle_throwable_if_none_thrown(
        AlarmScheduler $alarmScheduler,
        AlarmHandler $alarmHandler,
        ThrowableHandler $throwableHandler
    ): void {
        $throwableHandler->handle(Argument::any())->shouldNotBeCalled();

        $alarmHandler->handle($alarmScheduler)->shouldBeCalled();

        $this->handle($alarmScheduler);
    }

    public function it_should_handle_throwable_when_thrown(
        AlarmScheduler $alarmScheduler,
        AlarmHandler $alarmHandler,
        ThrowableHandler $throwableHandler
    ): void {
        $e = new Exception();

        $alarmHandler->handle($alarmScheduler)->willThrow($e);

        $throwableHandler->handle($e)->shouldBeCalled();

        $this->handle($alarmScheduler);
    }

    public function it_should_not_throw_anything_from_throwable_handler(
        AlarmScheduler $alarmScheduler,
        AlarmHandler $alarmHandler,
        ThrowableHandler $throwableHandler
    ): void {
        $alarmHandlerException = new Exception();
        $throwableHandlerException = new Exception();

        $alarmHandler->handle($alarmScheduler)->willThrow($alarmHandlerException);

        $throwableHandler->handle($alarmHandlerException)->willThrow($throwableHandlerException);

        $this->handle($alarmScheduler);
    }
}
