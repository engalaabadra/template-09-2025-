<?php

namespace App\Models\Filters;

use App\Classes\Filter\Filter;
use App\Classes\Filter\FilterTypeEnum;
use App\Enums\IsActiveEnum;
use Illuminate\Contracts\Support\Arrayable;
use App\Models\Traits\EnumOptionsTrait;
use Illuminate\Support\Facades\Cache;

/**
 * Class LangFilter
 *
 * Represents a language filter used in the frontend dropdowns or filtering mechanisms.
 * This filter defines the UI type, placeholder, data type, and optionally accepts
 * a custom callback for applying advanced filtering logic.
 */
final class LangFilter extends Filter
{
    use EnumOptionsTrait;
    /**
     * The key used in the frontend to identify this filter (usually the database column).
     *
     * @var string
     */
    public string $key = 'lang';

    /**
     * Defines the UI type of the filter in the frontend (dropdown, checkbox, range, etc.).
     *
     * @var FilterTypeEnum
     */
    public FilterTypeEnum $filterTypeEnum = FilterTypeEnum::DROPDOWN;

    /**
     * The translation key for the dropdown placeholder.
     * Used for display text like "Select language" in the UI.
     *
     * @var string|null
     */
    public ?string $placeholder = 'message.lang_select';

    /**
     * Indicates whether the selected values should be treated as integers.
     * Useful for enum-based filters or when the backend expects integers (e.g., 0/1).
     *
     * @var bool
     */
    public bool $isInt = true;

    /**
     * Optional closure that allows custom filtering logic instead of the default behavior.
     *
     * @param \Closure|null $callback  A custom filtering callback (optional).
     */
    public function __construct(public ?\Closure $callback = null)
    {
        // Initialize the filter with a custom callback if provided.
    }


    /**
     * Returns the available dropdown options for the language filter.
     *
     * Typically pulls values from a supported languages configuration source.
     * The return format may be like:
     *   [ ['id' => 'en', 'name' => 'English'], ['id' => 'ar', 'name' => 'Arabic'] ]
     *
     * @return null|Arrayable|array|string
     */
    public static function getData(): null|Arrayable|array|string
    {
        $langs =cache()->rememberForever('supported_languages', function () {
            return array_keys(config('app.supported_languages', []));
        });
        return self::getLangOptions($langs);
    }
}



