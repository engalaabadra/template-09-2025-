<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

/**
 * Custom validation rule to ensure a translated value is unique per language.
 * Useful when saving multilingual records with lang/translate_id structure.
 */
class UniqueTranslationValue implements Rule
{
    /**
     * The table name to check against.
     *
     * @var string
     */
    protected string $table;

    /**
     * The column name to check for uniqueness.
     *
     * @var string
     */
    protected string $column;

    /**
     * Optional ID to ignore during validation (for update scenarios).
     *
     * @var int|null
     */
    protected ?int $ignoreId;

    /**
     * Optional translation group ID (translate_id) to allow duplicates within the same group.
     *
     * @var int|null
     */
    protected ?int $translateId;

    /**
     * Create a new rule instance.
     *
     * @param string $table The table to query.
     * @param string $column The column to check for uniqueness.
     * @param int|null $ignoreId ID to ignore (used when updating a record).
     * @param int|null $translateId Translation group ID to allow duplicates within the group.
     */
    public function __construct(string $table, string $column, ?int $ignoreId = null, ?int $translateId = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->translateId = $translateId;
    }


     /**
     * This rule ensures that the given value is unique **per language** across the entire table.
     * It prevents duplicate values in the same language, even for different translate_id groups.
     * 
     * Allowed: Same value in different languages
     * Allowed: Same value for the same record (on update)
     * Not allowed: Same value in the same language for any other record
     * 
     * Summery : not allowed store same a value in ((((same lang)))) in table
     * 
     * 
     * @param string $attribute The input attribute being validated.
     * @param mixed $value The value of the attribute.
     * @return bool Whether the value is unique in the given context.
     * 
     */
    public function passes($attribute, $value): bool
    {
        // Extract language code from the attribute name (e.g., 'translations.0.title' → 'ar')
        $lang = $this->extractLangFromAttribute($attribute);

        // we want uniqueness across all items , only in this lang , to know if exist conflict in same lang or no
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where('lang', $lang);
        // Ignore current record (only when updating)

        if ($this->ignoreId) {
            $query->where('translate_id', '!=', $this->ignoreId);
        }

        // If any such row exists, it means the value is already taken in this lang
        return !$query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string Error message if the rule fails.
     */
    public function message(): string
    {
        return __('validation.unique');
    }


    
    /**
     * Extract the language code from a given attribute name in a translations array.
     *
     *  Example:
    * - If the attribute is "translations.0.title", this method finds index 0 and returns
    *   the corresponding "lang" value from request()->input('translations').
    * - If no language is found, it falls back to the application's default locale.
    * 
     * Example: 'translations.0.title' will extract 'ar' or 'fr' depending on the request.
     *
     * @param string $attribute The attribute name from the request (e.g., 'translations.0.title').
     * @return string The language code found in the translations array, or the default app locale if not found.
     */
    protected function extractLangFromAttribute(string $attribute): string
    {
        // Split the attribute name by '.' to get individual parts
        $parts = explode('.', $attribute);

        // Initialize index variable to null
        $index = null;

        // Loop through each part to find the numeric index
        foreach ($parts as $part) {
            if (is_numeric($part)) {
                // Convert the numeric part to integer and assign to index
                $index = (int) $part;
                break; // stop after finding the first numeric index
            }
        }

        // Check if a valid index was found
        if ($index !== null) {
            // Get the 'translations' input from the request
            $translations = request()->input('translations', []);

            // If translations are passed as JSON string, decode them into array
            if (is_string($translations)) {
                $translations = json_decode($translations, true);
            }


            // Ensure translations is a valid array
            if (!is_array($translations)) {
                $translations = [];
            }
            // Check if the language key exists at the given index
            if (isset($translations[$index]['lang'])) {
                return $translations[$index]['lang']; // Return the found language
            }
        }

        // Fallback: return the default application locale if language not found
        return localeLang();
    }
}
