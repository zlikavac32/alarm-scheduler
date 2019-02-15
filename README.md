# Alarm scheduler

This library provides support for multiple `SIGALRM` handlers.

## Table of contents

1. [Introduction](#introduction)
1. [Installation](#installation)
1. [API](#api)
    1. [AlarmHandler](#alarmhandler)
    1. [AlarmScheduler](#alarmscheduler)
    1. [InterruptException](#interruptexception)
    1. [InterruptAlarmHandler](#interruptalarmhandler)
1. [Usage](#usage)
1. [Rule of thumb](#rule-of-thumb)
1. [Examples](#examples)

## Introduction

As of PHP 7.1, async signals are supported through [pcntl_async_signals()](http://php.net/manual/en/function.pcntl-async-signals.php) function. With that nice feature, [pcntl_alarm()](http://php.net/manual/en/function.pcntl-alarm.php) became more helpful than ever.

Only issue is, we can set one signal handler per process.

This library provides simple scheduler for `SIGALRM` and allows multiple targets to be scheduled with some arbitrary delay.

## Installation

Recommended installation is through Composer.

```bash
composer require zlikavac32/alarm-scheduler
```

## API

Two interfaces are provided, one for alarm handler and the other for the alarm scheduler.

Do note that this library does not call `pcntl_async_signals(true);`. It's the responsibility of the library user to call it where they find it applicable.

### AlarmHandler

Interface `\Zlikavac32\AlarmScheduler\AlarmHandler` is used to implement alarm handler. Method `handle()` will be called from the signal handler so check [Rule of thumb](#rule-of-thumb) for more info.

Current scheduler is passed into `handle()` to allow rescheduling of the handler (or scheduling a new one).

## AlarmScheduler

Interface `\Zlikavac32\AlarmScheduler\AlarmScheduler` is used to implement alarm scheduler. Implementation should take control over `SIGALRM` handling.

Methods must be safe to be called from within the signal handler. Check [Rule of thumb](#rule-of-thumb) for more info.

## InterruptException

Exception representing hard interrupt that must be respected by the scheduler implementation.

It can be thrown from the alarm handler in order to cause exception from the signal handler.

Users are not restricted to this exception for the hard interrupt as described in the [Rule of thumb](#rule-of-thumb) section.

## InterruptAlarmHandler

Causes hard interrupt from alarm scheduler by throwing `InterruptException`

## Usage

First create the scheduler (currently, only single implementation is provided).

```php
$scheduler = new \Zlikavac32\AlarmScheduler\NaiveAlarmScheduler();
```

When it's applicable to take over of `SIGALRM` handling, call

```php
$scheduler->start();
```

Method `start()` must be called before any additional use of scheduler methods.

Then implement some alarm handler.

```php
$handler = new class implements \Zlikavac32\AlarmScheduler\AlarmHandler {

   public function handle(\Zlikavac32\AlarmScheduler\AlarmScheduler $scheduler): void {
       echo (new DateTime())->format('i\\m:s\\s'), "\n";
   }
};
```

Next, schedule alarm handler.

```php
$scheduler->schedule(5, $handler); // to run $handler in 5 seconds
```

Now, this will not block. If script reaches end before handlers are triggered, it will exit without triggering them.

For testing purposes, we can sleep for a few seconds. We can also print current time.

```php
echo (new DateTime())->format('i\\m:s\\s'), "\n";

$sleep = 7;
while ($sleep = sleep($sleep));
```

## Rule of thumb

If `SIGALRM` is not blocked, it can interrupt scheduler methods at any point. Scheduler implementations must be safe to be run from within the signal handler. In most cases that means blocking signal until scheduler method is to be finished, and then unblocking it. PHP VM takes care of the rest.

No exception, except `\Zlikavac32\AlarmScheduler\InterruptException`, should be thrown from the alarm handler. If exception is thrown, and scheduler implementation does not catch it (which is not the requirement), it could leave the scheduler in inconsistent state.

If custom async exception is a requirement, alternative is to use `SIGUSR1` or `SIGUSR2` to throw exception from a different stack frame. PHP VM will deffer signal handling until current handler finishes. That means it will not affect scheduler stack frame.

```php
pcntl_signal(SIGUSR1, function (): void {
    throw new SomeDomainSpecificException();
});

$scheduler->schedule(2, new class implements \Zlikavac32\AlarmScheduler\AlarmHandler {

    public function handle(\Zlikavac32\AlarmScheduler\AlarmScheduler $scheduler): void {
        posix_kill(getmypid(), SIGUSR1);
    }
});
```

`sleep()` may not be safe to use with `SIGALRM` so check how your system handles sleeping before using this library.

## Examples

Examples with code comments can be found in [examples](/examples) directory.
