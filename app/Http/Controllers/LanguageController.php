<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use App\Traits\Controllers\WebApiSuccessResponseTrait;

/**
 * Class LanguageController
 *
 * This controller handles language switching, retrieving the default language,
 * and listing all supported languages from the configuration file.
 */
class LanguageController extends Controller
{
    // Trait for formatting standard API responses
    use WebApiSuccessResponseTrait;

    /**
     * Switch the application's language.
     *
     * @param string $lang  The selected language key
     * @return \Illuminate\Http\JsonResponse
     */
    public function switchLang($lang)
    {
        // Check if the requested language exists in the config file
        if (array_key_exists($lang, Config::get('languages'))) {
            // Store the selected language in a file
            Cache::put('applocale', $lang);

            // Set the app locale using the stored value
            app()->setLocale(Cache::get('applocale'));

            // Return the current locale in the response
            return $this->respond(localeLang());
        }
    }

    /**
     * Get the current default application language.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function defaultLang()
    {
        return $this->respond(defaultLang());
    }

    /**
     * Get all supported languages defined in config.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllLangs()
    {
        $getAllLangs = Cache::get('supported_languages');
        return $this->respond($getAllLangs);
    }
}
