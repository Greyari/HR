<?php

if (!function_exists('env_clean')) {
    function env_clean(string $key, $default = null)
    {
        $value = getenv($key) ?: $default;
        // Hapus tanda kutip jika ada
        return is_string($value) ? trim($value, '"') : $value;
    }
}
