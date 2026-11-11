<?php

namespace App\Support;

class Lang {
    public static function get(string $key): string {
        $lang = require __DIR__ . '/../../lang/en.php';
        return $lang[$key] ?? 'Translation not found.';
    }
}