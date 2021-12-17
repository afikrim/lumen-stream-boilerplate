<?php

namespace App\Console\Commands\Stream;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ConsumeCommand extends Command
{
    protected $signature = 'stream:consume
                            {key : Specified stream key}
                            {--count=5 : A number of event that will retrieve}
                            {--block=2000 : Blocking timeout of reading command in milis}
                            {--rest=3 : Delay between each read in seconds}';

    protected $description = 'Destroy an object from the stream';

    public function handle(): void
    {
        if (!$this->hasArgument('key')) {
            echo "Key params cannot be null.";
            return;
        }

        try {
            // create consumer group
            Artisan::call('stream:declare-group', [
                'key' => $this->argument('key'),
                'group' => $this->getGroup(),
            ]);
        } catch (\Exception$e) {
            // do nothing
        }

        while (true) {
            $result = $this->readStream();
            if (!$result) {
                continue;
            }

            $objects = $this->parseResult($result);

            $objects->each(function ($object) {
                $this->processData($object);

                $this->ackStream($object->id);
            });

            $this->rest();
        }
    }

    protected function parseResult(array $result)
    {
        $objects = collect($result[0][1])
            ->map(function ($result) {
                $object = collect($result)
                    ->reduce(function ($prev, $raw, $index) {
                        if ($index === 0) {
                            $prev->id = $raw;
                            return $prev;
                        }

                        [$field, $value] = $raw;
                        $prev->{$field} = $value;

                        return $prev;
                    }, new \stdClass());

                return $object;
            });

        return $objects;
    }

    protected function processData(\stdClass$data)
    {
        // Write your handle here.
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }

    protected function getGroup()
    {
        return env('STREAM_GROUP', Str::slug(config('app.env') . '_' . config('app.name') . '_group', '_'));
    }

    protected function getConsumer()
    {
        return env('STREAM_CONSUMER', Str::slug(config('app.env') . '_' . config('app.name') . '_consumer', '_'));
    }

    private function readStream()
    {
        $result = Redis::executeRaw(
            [
                'XREADGROUP',
                'GROUP',
                $this->getGroup(),
                $this->getConsumer(),
                'BLOCK',
                $this->option('block'),
                'COUNT',
                $this->option('count'),
                'STREAMS',
                $this->argument('key'),
                '>',
            ]
        );

        if ($result && is_string($result)) {
            throw new \Exception($result);
        }

        return $result;
    }

    private function ackStream($id)
    {
        $_ack = Redis::executeRaw(
            [
                'XACK',
                $this->argument('key'),
                $this->getGroup(),
                $id,
            ]
        );
    }

    private function rest()
    {
        sleep($this->option('rest'));
    }
}
