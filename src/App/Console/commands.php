<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Embedded Queue Workers
|--------------------------------------------------------------------------
|
| When QUEUE_EMBEDDED_WORKERS is enabled, the scheduler spawns queue workers
| every minute. This is designed for shared hosting and environments without
| persistent daemon support. Each worker processes jobs then exits gracefully.
|
*/

if (config('queue.embedded_workers.enabled')) {
    $config = config('queue.embedded_workers');

    for ($i = 1; $i <= $config['workers']; $i++) {
        Schedule::command('queue:work', [
            '--stop-when-empty',
            '--max-time='.$config['max_time'],
            '--max-jobs='.$config['max_jobs'],
            '--memory='.$config['memory'],
            '--sleep='.$config['rest'],
            '--queue='.$config['queues'],
        ])
            ->everyMinute()
            ->withoutOverlapping(expiresAt: $config['max_time'] + 5)
            ->runInBackground()
            ->name("queue-worker-{$i}");
    }
}
