<?php

namespace App\Support;

class IdObfuscator
{
    private const ALPHABET = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

    public static function encode(int $id, ?string $context = null): string
    {
        if ($id <= 0) {
            return '';
        }

        $appKey = (string) config('app.key', 'clinic-salt-fallback-key');
        $salt = $context ? hash_hmac('sha256', $context, $appKey, true) : hash('sha256', $appKey, true);

        $left = ($id >> 16) & 0xFFFF;
        $right = $id & 0xFFFF;

        for ($i = 0; $i < 4; $i++) {
            $roundHash = hash_hmac('sha256', "round_{$i}_{$right}", $salt, true);
            $f = unpack('n', substr($roundHash, 0, 2))[1];
            $newLeft = $right;
            $newRight = $left ^ $f;
            $left = $newLeft;
            $right = $newRight;
        }

        $obfuscated = (($left & 0xFFFF) << 16) | ($right & 0xFFFF);

        $checksumHash = hash_hmac('sha256', (string) $obfuscated, $salt, true);
        $checksum = unpack('n', substr($checksumHash, 0, 2))[1] & 0x0FFF;

        $combined = ($obfuscated << 12) | $checksum;

        return self::toBase($combined);
    }

    public static function decode(string $token, ?string $context = null): ?int
    {
        if (trim($token) === '') {
            return null;
        }

        $appKey = (string) config('app.key', 'clinic-salt-fallback-key');
        $salt = $context ? hash_hmac('sha256', $context, $appKey, true) : hash('sha256', $appKey, true);

        $combined = self::fromBase(trim($token));
        if ($combined === null || $combined <= 0) {
            return null;
        }

        $checksum = $combined & 0x0FFF;
        $obfuscated = $combined >> 12;

        $checksumHash = hash_hmac('sha256', (string) $obfuscated, $salt, true);
        $expectedChecksum = unpack('n', substr($checksumHash, 0, 2))[1] & 0x0FFF;

        if ($checksum !== $expectedChecksum) {
            return null;
        }

        $left = ($obfuscated >> 16) & 0xFFFF;
        $right = $obfuscated & 0xFFFF;

        for ($i = 3; $i >= 0; $i--) {
            $prevRight = $left;
            $roundHash = hash_hmac('sha256', "round_{$i}_{$prevRight}", $salt, true);
            $f = unpack('n', substr($roundHash, 0, 2))[1];
            $prevLeft = $right ^ $f;
            $left = $prevLeft;
            $right = $prevRight;
        }

        $id = (($left & 0xFFFF) << 16) | ($right & 0xFFFF);

        return $id > 0 ? $id : null;
    }

    public static function decodeOrRaw(mixed $value, ?string $context = null): mixed
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $decoded = self::decode($value, $context);
        if ($decoded !== null) {
            return $decoded;
        }

        if (ctype_digit(trim($value))) {
            return (int) $value;
        }

        return $value;
    }

    private static function toBase(int $num): string
    {
        $base = strlen(self::ALPHABET);
        $str = '';
        while ($num > 0) {
            $rem = $num % $base;
            $str = self::ALPHABET[$rem].$str;
            $num = intdiv($num, $base);
        }

        return str_pad($str, 8, self::ALPHABET[0], STR_PAD_LEFT);
    }

    private static function fromBase(string $str): ?int
    {
        $base = strlen(self::ALPHABET);
        $num = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos(self::ALPHABET, $str[$i]);
            if ($pos === false) {
                return null;
            }
            $num = ($num * $base) + $pos;
        }

        return $num;
    }
}
