<?php

namespace App\Libraries;

class UserContext
{
    private static ?array $user = null;

    public static function set(array $user): void
    {
        self::$user = $user;
    }

    public static function get(): ?array
    {
        return self::$user;
    }
}
