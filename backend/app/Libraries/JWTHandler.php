<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class JWTHandler
{
    private string $secret;
    private int $expire;
    private int $refreshExpire;
    private string $algo = 'HS256';

    public function __construct()
    {
        $this->secret       = env('jwt.secret', 'gyminix_secret');
        $this->expire       = (int) env('jwt.expire', 3600);
        $this->refreshExpire = (int) env('jwt.refresh_expire', 604800);
    }

    public function generateAccessToken(array $payload): string
    {
        $now = time();
        $data = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $this->expire,
            'type' => 'access',
        ]);
        return JWT::encode($data, $this->secret, $this->algo);
    }

    public function generateRefreshToken(array $payload): string
    {
        $now = time();
        $data = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $this->refreshExpire,
            'type' => 'refresh',
        ]);
        return JWT::encode($data, $this->secret, $this->algo);
    }

    public function decode(string $token): ?stdClass
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getExpireTime(): int
    {
        return $this->expire;
    }

    public function getRefreshExpireTime(): int
    {
        return $this->refreshExpire;
    }
}
