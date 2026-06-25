<?php

use Carbon\Carbon;
use App\Enums\ActivatedRestoredActionEnum;

/**
 * ===========================================
 *  GENERAL & REQUEST HELPERS
 * ===========================================
 */

if (!function_exists('isWebRequest')) {
    /**
     * Determine if the current request is a web (non-API) request.
     *
     * @return bool True if web request (expects HTML), false if API (expects JSON).
     */
    function isWebRequest(): bool
    {
        // If request does not expect JSON, treat it as a web request
        return !request()->expectsJson();
    }
}

if (!function_exists('localeLang')) {
    /**
     * Get the current active application locale.
     *
     * @return string
     */
    function localeLang()
    {
        return app()->getLocale();
    }
}

if (!function_exists('defaultLang')) {
    /**
     * Get the default application locale from config.
     *
     * @return string
     */
    function defaultLang()
    {
        return config('app.locale');
    }
}

if (!function_exists('supportedLanguages')) {
    /**
     * Get supported languages from config.
     *
     * @return array
     */
    function supportedLanguages()
    {
        return config('app.supported_languages');
    }
}

if (!function_exists('systemCurrency')) {
    /**
     * Get the default system currency code.
     *
     * @return string
     */
    function systemCurrency()
    {
        return 'SAR';
    }
}

if (!function_exists('countryCurrency')) {
    /**
     * Get currency based on IP geolocation.
     *
     * @return string|null
     */
    function countryCurrency()
    {
        return geoip(request()->ip())->currency;
    }
}

if (!function_exists('isDefaultLocale')) {
    /**
     * Check if given language is the default locale.
     *
     * @param string $lang
     * @return bool
     */
    function isDefaultLocale($lang): bool
    {
        return $lang === config('app.locale');
    }
}

if (!function_exists('urlFlag')) {
    /**
     * Generate a URL for the country flag based on ISO code.
     *
     * @param string $code
     * @return string
     */
    function urlFlag($code)
    {
        return 'https://ipdata.co/flags/' . $code . '.png';
    }
}

if (!function_exists('page')) {
    /**
     * Get current page number from request input.
     *
     * @return mixed
     */
    function page()
    {
        return request()->input('page');
    }
}

if (!function_exists('query')) {
    /**
     * Get query keyword from request input.
     *
     * @return mixed
     */
    function query()
    {
        return request()->input('query');
    }
}

if (!function_exists('clientId')) {
    /**
     * Get client_id from request input.
     *
     * @return mixed
     */
    function clientId()
    {
        return (int)request()->input('client_id');
    }
}

if (!function_exists('active')) {
    /**
     * Get active status from request input.
     *
     * @return mixed
     */
    function active()
    {
        return request()->input('active');
    }
}

if (!function_exists('status')) {
    /**
     * Get status from request input.
     *
     * @return mixed
     */
    function status()
    {
        return request()->input('status');
    }
}

if (!function_exists('message')) {
    /**
     * Get message from request input.
     *
     * @return mixed
     */
    function message()
    {
        return request()->input('message');
    }
}

if (!function_exists('rate')) {
    /**
     * Get rate value from request input.
     *
     * @return mixed
     */
    function rate()
    {
        return request()->input('rate');
    }
}

if (!function_exists('fav')) {
    /**
     * Get favorite flag from request input.
     *
     * @return mixed
     */
    function fav()
    {
        return request()->input('fav');
    }
}

if (!function_exists('type')) {
    /**
     * Get type value from request input.
     *
     * @return mixed
     */
    function type()
    {
        return request()->input('type');
    }
}

if (!function_exists('login_type')) {
    /**
     * Get login type from request input.
     *
     * @return mixed
     */
    function login_type()
    {
        return request()->input('login_type');
    }
}

if (!function_exists('randomLink')) {
    /**
     * Get random link value from request input.
     *
     * @return mixed
     */
    function randomLink()
    {
        return request()->input('link');
    }
}

if (!function_exists('total')) {
    /**
     * Get the 'total' input value from request, or default to 10.
     *
     * @return int
     */
    function total()
    {
        return request()->get('total', 10);
    }
}

if (!function_exists('currentTime')) {
    /**
     * Get current time as string (HH:MM:SS).
     *
     * @return string
     */
    function currentTime()
    {
        return Carbon::now()->toTimeString();
    }
}

if (!function_exists('currentDate')) {
    /**
     * Get current date as string (YYYY-MM-DD).
     *
     * @return string
     */
    function currentDate()
    {
        return Carbon::now()->toDateString();
    }
}

if (!function_exists('getCode')) {
    /**
     * Generate a random code based on the app environment.
     *
     * @return string
     */
    function getCode(): string
    {
        return env('APP_ENV') === 'production' ? (string) mt_rand(1000, 9999) : '0000';
    }
}

if (!function_exists('filePath')) {
    /**
     * Convert a public URL to its internal storage path.
     *
     * @param string $url
     * @return string
     */
    function filePath($url)
    {
        return 'public/' . ltrim($url, '/storage/');
    }


}

if (!function_exists('normalizeActivatedField')) {
    /**
     * Normalize field by removing only "_activated" (and optional number before it).
     */
    function normalizeActivatedField(string $value): string
    {
        $activated = ActivatedRestoredActionEnum::_ACTIVATED->value;
        return preg_replace('/(_\d+)?' . $activated . '(?=(_|$))/', '', $value);
    }
}

if (!function_exists('normalizeRestoredField')) {

    /**
     * Normalize field by removing only "_restored" (and optional number before it).
     */
    function normalizeRestoredField(string $value): string
    {
        $restored = ActivatedRestoredActionEnum::_RESTORED->value;
        return preg_replace('/(_\d+)?' . $restored . '(?=(_|$))/', '', $value);

    }
}

if (!function_exists('extractIdFromValue')) {

    function extractIdFromValue($value): ?int
    {
        //Match the first number between underscores after the name
        if (preg_match('/_(\d+)_/', $value, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

}