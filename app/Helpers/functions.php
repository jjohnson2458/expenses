<?php

/**
 * VQ Money — Global Helper Functions
 */

if (!function_exists('format_currency')) {
    function format_currency(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}

if (!function_exists('format_date')) {
    function format_date(string $date, string $format = 'M j, Y'): string
    {
        return date($format, strtotime($date));
    }
}

if (!function_exists('__t')) {
    /**
     * Translation helper compatible with old views.
     * Maps to Laravel's __() helper using the messages file.
     */
    function __t(string $key, string $default = ''): string
    {
        $translated = __("messages.{$key}");
        // If Laravel returns the key unchanged, it wasn't found
        if ($translated === "messages.{$key}") {
            return $default ?: $key;
        }
        return $translated;
    }
}
