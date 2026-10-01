<?php

namespace App\Services;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class AuditoriaCifrado
{
    protected static function encrypter(): Encrypter
    {
        $key = config('audit.key');

        if (!$key) {
            throw new \RuntimeException('AUDIT_KEY no configurada');
        }

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (strlen($key) !== 32) {
            throw new \RuntimeException(
                'AUDIT_KEY inválida. Se requieren 32 bytes, se recibieron: ' . strlen($key)
            );
        }

        return new Encrypter($key, config('audit.cipher'));
    }

    public static function encrypt(array $data): string
    {
        return self::encrypter()->encrypt(json_encode($data));
    }

    public static function decrypt(string $payload): array
    {
        return json_decode(self::encrypter()->decrypt($payload), true);
    }
    public static function safeDecrypt($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return null;
        }

        try {
            return self::decrypt($value);
        } catch (\Throwable $e) {

            return json_decode($value, true);
        }
    }
}
