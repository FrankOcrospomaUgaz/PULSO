<?php

namespace App\Services;

use Ramsey\Uuid\Uuid;

class UuidService
{
    public static function uuidv4()
    {
        return Uuid::uuid4();
    }
}
