<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class StreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            \App\Console\Commands\Stream\AddCommand::class,
            \App\Console\Commands\Stream\ConsumeCommand::class,
            \App\Console\Commands\Stream\DeclareGroupCommand::class,
            \App\Console\Commands\Stream\DelCommand::class,
            \App\Console\Commands\Stream\DestroyGroupCommand::class,
        ]);
    }

}
