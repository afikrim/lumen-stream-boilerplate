<?php

namespace App\Console\Commands\Stream;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ConsumeCommand extends Command
{
    protected $signature = 'stream:consume
                            {key : Specified stream key}';

    protected $description = 'Destroy an object from the stream';

    public function handle(): void
    {
        if (!$this->hasArgument('key')) {
            echo "Key params cannot be null.";
            return;
        }

        $group = Str::slug(config('app.env') . '_' . config('app.name') . '_group', '_');
        $consumer = Str::slug(config('app.env') . '_' . config('app.name') . '_consumer', '_');

        try {
            // create consumer group
            Artisan::call('stream:declare-group', [
                'key' => $this->argument('key'),
                'group' => $group,
            ]);
        } catch (\Exception$e) {
            // do nothing
        }

        $result = Redis::executeRaw(
            [
                'XREADGROUP',
                'GROUP',
                $group,
                $consumer,
                'BLOCK',
                '0',
                'COUNT',
                '1',
                'STREAMS',
                $this->argument('key'),
                '>',
            ]
        );

        if (is_string($result)) {
            throw new \Exception($result);
        }

        $data = $this->parseResult($result);
        $result = Redis::executeRaw(
            [
                'XACK',
                $this->argument('key'),
                $group,
                $data->id,
            ]
        );

        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function parseResult(array $result) {
        $data = new \stdClass();
        foreach($result[0][1][0] as $index => $value) {
            if ($index === 0) {
                $data->id = $value;
                continue;
            }

            $data->{$value[0]} = $value[1];
        }

        return $data;
    }
}
