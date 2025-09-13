<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;

/**
 * Trait EnumOptionsTrait
 *
 * Provides helper methods for working with PHP Enums:
 *  - Generating options for dropdowns
 *  - Translating enum values
 *  - Fetching random cases
 *  - Mapping values to names
 * used to convert language codes into collections with IDs, codes, and translated names.
 *
 * Common use cases:
 *   - Create select/dropdown options for forms.
 *   - Translate enum values to a readable label.
 *   - Retrieve enum values or names easily.
 */

trait EnumOptionsTrait
{
    #region ===================== Start Languages Section =====================
   
    /**
     * Get a collection of language options with ID, code, and translated name.
     *
     * This method transforms an array of language codes (e.g. ['ar', 'en'])
     * into a standardized collection that can be used in dropdowns or API responses.
     *
     * Example:
     * ```php
     * $langs = ['ar', 'en'];
     * $options = self::getLangOptions($langs);
     * // Result:
     * // [
     * //   ['id' => 1, 'code' => 'ar', 'name' => 'العربية'],
     * //   ['id' => 2, 'code' => 'en', 'name' => 'English']
     * // ]
     * ```
     *
     * @param array $langs Array of language codes (e.g. ['ar', 'en']).
     * @return \Illuminate\Support\Collection
     */
    public static function getLangOptions(array $langs): \Illuminate\Support\Collection
    {
        return collect($langs)->map(function ($lang, $index) {
            return [
                'id'   => $index + 1, // Numeric ID or UUID
                'code' => $lang, // Language code like 'ar', 'en'
                'name' => __(self::getLangTranslationKey($lang)), // Translated name
            ];
        });
    }

    /**
     * Generate the translation key for a given language code.
     *
     * @param string $lang Language code.
     * @return string Translation key in the format "languages.{code}".
     */
    protected static function getLangTranslationKey(string $lang): string
    {
        return "languages.$lang"; // Example: languages.ar, languages.en
    }

    #endregion ===================== End Languages Section =====================

    #region ===================== Start Options Section (for render in frontend, which can be used for form dropdowns or filters) =====================

    /**
     * Get options where "id" is the Enum object itself and "name" is the translated label.
     *
     * @param array|null $items Optional list of enum cases. Defaults to all cases.
     * @return Collection Collection of ['id' => EnumObject, 'name' => translatedName]
     *
     * @example
     *   IsActiveEnum::getOptionsData();
     *   // [
     *   //    ['id' => IsActiveEnum::ACTIVE, 'name' => 'Active'],
     *   //    ['id' => IsActiveEnum::NOT_ACTIVE, 'name' => 'Inactive']
     *   // ]
     */
    public static function getOptionsData(?array $items = null): Collection
    {
        // Convert given items or all cases into a collection
        return collect($items ?? self::cases())->map(function ($row) { // self::cases() ,ex: IsActiveEnum::ACTIVE
            $item['id'] = $row;                 // Use the enum case itself as the ID , like: IsActiveEnum::ACTIVE
            $item['name'] = self::getTrans($row); // Translated label
            return $item;
        });
    }

    /**
     * Get a random enum case.
     *
     * @return mixed Random enum case.
     *
     * @example
     *   IsActiveEnum::getRandomCase();
     *   // IsActiveEnum::ACTIVE or IsActiveEnum::NOT_ACTIVE
     */
    public static function getRandomCase()
    {
        return collect(self::cases())->random(); // get all cases but randomly
    }

    /**
     * Get options where "id" is the enum value and "name" is the translated label.
     *
     * @return Collection Collection of ['id' => value, 'name' => translatedName]
     *
     * @example
     *   IsActiveEnum::getOptionsIdNameData();
     *   // [
     *   //    ['id' => 1, 'name' => 'Active'],
     *   //    ['id' => 0, 'name' => 'Inactive']
     *   // ]
     */
    public static function getOptionsIdNameData(): Collection
    {
        return collect(self::cases())->map(function ($row) {
            $item['id'] = $row->value;           // The enum's actual value(IsActiveEnum::ACTIVE -> value this : 1)
            $item['name'] = self::getTrans($row); // Translated label -> active
            return $item;
        });
    }
    
    /**
     * Get an array [value => translatedName].
     *
     * @return array
     *
     * @example
     *   IsActiveEnum::getOptionsPluckData();
     *   // [1 => 'Active', 0 => 'Inactive']
     */
    public static function getOptionsPluckData(): array
    {
        $options = self::getOptionsData()->toArray(); // Convert collection to array
        return array_combine(self::values(), array_column($options, 'name')); // Map values to translated names
    }
    #endregion ===================== End Options Section =====================

    #region ===================== Start Translation Section =====================

    /**
     * Translate the current enum instance.
     *
     * @param string|null $local Optional locale.
     * @return string
     *
     * @example
     *   IsActiveEnum::ACTIVE->text();
     *   // "Active"
     */
    public function text(?string $locale = null): string
    {
        return self::getTrans($this, $locale);
    }

     /**
     * Translate the current enum instance.
     *
     * @param string|null $local Optional locale.
     * @return string
     *
     * @example
     *   IsActiveEnum::ACTIVE->translate();
     *   // "Active"
     */
    public function translate($local = null): string
    {
        return self::getTrans($this, $local);
    }

    /**
     * Translate a specific enum case or value.
     *
     * @param mixed $case Enum case or value.
     * @param string|null $locale Optional locale.
     * @return string|null Translated text or null if not found.
     *
     * @example
     *   IsActiveEnum::getTrans(IsActiveEnum::ACTIVE);
     *   // "Active"
     */
    public static function getTrans($case = null, $locale = null): ?string
    {
        if (!$case) return null;

        //class_basename(__CLASS__) : IsActiveEnum, ($case?->value ?? $case) : 1-> result : enums.IsActiveEnum.1 -> result: 'Active'
        $value = __('enums.' . class_basename(__CLASS__) . '.' . ($case?->value ?? $case), [], $locale);

        // If translation key was not found, return original value
        if (str_contains($value, 'enums'))
            return $case->value;

        return $value;
    }
    #endregion ===================== End Translation Section =====================
    
    #region ===================== Start Values, names Enum Classes Section =====================

    /**
     * Find an enum case by its value.
     *
     * @param mixed $value The value to search for.
     * @return mixed|null Enum case or null.
     *
     * @example
     *   IsActiveEnum::getEnumFromValue(1);
     *   // IsActiveEnum::ACTIVE
     */
    public static function getEnumFromValue($value)
    {
        return collect(self::cases())->where('value', $value)->first();
    }

    
    /**
     * Get the enum class name without namespace.
     *
     * @return string
     *
     * @example
     *   IsActiveEnum::getFileName();
     *   // "IsActiveEnum"
     */
    public static function getFileName(): string
    {
        return class_basename(self::class);
    }

    /**
     * Get enum names.
     *
     * @return array
     *
     * @example
     *   IsActiveEnum::names();
     *   // ["ACTIVE", "NOT_ACTIVE"]
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get enum values.
     *
     * @return array
     *
     * @example
     *   IsActiveEnum::values();
     *   // [1, 0]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the actual value of the current enum instance.
     *
     * @return string
     *
     * @example
     *   IsActiveEnum::ACTIVE->getValue();
     *   // 1
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get an array [value => name].
     *
     * @return array
     *
     * @example
     *   IsActiveEnum::array();
     *   // [1 => "ACTIVE", 0 => "NOT_ACTIVE"]
     */
    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }
    #endregion ===================== End Values, names Enum Classes Section =====================

}
