<?php

namespace Fenrir\Authentication\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public function __construct(
        private string $key,
        private string $alg = 'HS256'
    ) {}

    public function encode(mixed $payload): string
    {
        $encoded = JWT::encode($payload, $this->key, $this->alg);
        return $encoded;
    }
    public function decode(string $encoded): mixed
    {
        $decoded = JWT::decode($encoded, new Key($this->key, $this->alg));
        return $decoded;
    }
}
