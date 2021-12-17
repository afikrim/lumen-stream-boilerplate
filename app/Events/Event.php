<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Ramsey\Uuid\Uuid;

abstract class Event
{
    use SerializesModels;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }
}
