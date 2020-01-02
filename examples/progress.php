<?php

declare(strict_types=1);

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\NaiveAlarmScheduler;

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Example that demonstrates how to use SIGALRM to write async status to reduce output, while still having live output.
 */

pcntl_async_signals(true);

$scheduler = new NaiveAlarmScheduler();

$scheduler->start();

$soFar = 0;

$handler = new class($soFar) implements AlarmHandler
{

    private int $soFar;

    private int $prevSoFar = 0;

    public function __construct(int &$soFar)
    {
        $this->soFar = &$soFar;
    }

    public function handle(AlarmScheduler $scheduler): void
    {
        printf("%9d (%d iter/sec)\n", $this->soFar, ($this->soFar - $this->prevSoFar));

        $this->prevSoFar = $this->soFar;

        // run again in 1 second
        $scheduler->schedule(1, $this);
    }
};

// first schedule
$scheduler->schedule(1, $handler);

for ($i = 0; $i < PHP_INT_MAX; $i++) {
    $soFar = $i;
}

/*
Example output (one line every 1 second):

103682488 (103682488 iter/sec)
208903531 (105221043 iter/sec)
314557899 (105654368 iter/sec)
423412363 (108854464 iter/sec)
534600965 (111188602 iter/sec)
645501468 (110900503 iter/sec)
756063095 (110561627 iter/sec)
...
 */
