<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Stream\DeclareGroupCommand::class,
        \App\Console\Commands\Stream\DestroyGroupCommand::class,
        \App\Console\Commands\Stream\AddCommand::class,
        \App\Console\Commands\Stream\DelCommand::class,
        \App\Console\Commands\Stream\ConsumeCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
