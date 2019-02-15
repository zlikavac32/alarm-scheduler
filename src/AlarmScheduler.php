<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

/**
 * Should take control over SIGALRM handling on start().
 *
 * Signal handler should be changed and SIGALRM unblocked.
 *
 * Every implementation must catch InterruptException and rethrow upon signal handling completion. If multiple
 * exceptions are caught, any of them must be rethrown. Which one is implementation dependant.
 */
interface AlarmScheduler
{
    /**
     * Schedules $handler to be run after $timeout seconds. Call to schedule must not happen before at least $timeout
     * seconds have passed since the call to this method.
     *
     * It can, however, happen that handler is called after more than $timeout seconds.
     */
    public function schedule(int $timeout, AlarmHandler $handler): void;

    /**
     * Removes handler from schedule and cancels any pending action for that handler.
     *
     * If there are multiple schedules for the same handler, every instance must be removed
     */
    public function remove(AlarmHandler $handler): void;

    /**
     * Prepares environment for scheduler. Method responsible for taking over of SIGALRM handling
     */
    public function start(): void;

    /**
     * Perform any necessary cleanup and restore previous signal handler
     */
    public function finish(): void;
}
