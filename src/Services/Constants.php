<?php

namespace SIT\Search\Services;

class Constants
{
    public static function get_zoho_client_id(): string
    {
        if (!function_exists('get_field')) {
            return '';
        }
        return get_field('zoho_client_id', 'option') ?? '';
    }

    public static function get_zoho_client_secret(): string
    {
        if (!function_exists('get_field')) {
            return '';
        }
        return get_field('zoho_client_secret', 'option') ?? '';
    }

    public static function get_zoho_refresh_token(): string
    {
        if (!function_exists('get_field')) {
            return '';
        }
        return get_field('zoho_secret_code', 'option') ?? '';
    }
}