<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler;

/**
 * Implementations of this interface are intended to be called from within the {@see SIGALRM} handler, so rules that
 * apply to signal handlers, apply here as well.
 */
interface AlarmHandler
{
    /**
     * Throwing exception other than InterruptException has undefined behaviour and depends on the implementation. See
     * {@see AlarmScheduler} for more info.
     *
     * @throws InterruptException If interrupt should bubble up the stack.
     */
    public function handle(AlarmScheduler $scheduler): void;
}
