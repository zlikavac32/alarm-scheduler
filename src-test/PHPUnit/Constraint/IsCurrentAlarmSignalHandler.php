<?php

declare(strict_types=1);

namespace Zlikavac32\AlarmScheduler\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class IsCurrentAlarmSignalHandler extends Constraint
{
    protected function matches($other): bool
    {
        return $other === pcntl_signal_get_handler(SIGALRM);
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            '%s is same signal handler as %s',
            $this->siglalHandlerToString($other),
            $this->siglalHandlerToString(pcntl_signal_get_handler(SIGALRM))
        );
    }

    public function toString(): string
    {
        return 'is current alarm signal handler';
    }

    private function siglalHandlerToString($handler): string
    {
        if ($handler === SIG_IGN) {
            return 'SIG_IGN';
        } else {
            if ($handler === SIG_DFL) {
                return 'SIG_DFL';
            }
        }

        return $this->exporter->export($handler);
    }
}
