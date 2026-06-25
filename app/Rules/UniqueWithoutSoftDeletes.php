<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to ensure a value is unique in a table,
 * ignoring soft-deleted records (i.e., where `deleted_at` is NULL).
 * Optionally supports ignoring a specific ID (for update scenarios).
 */
class UniqueWithoutSoftDeletes implements Rule
{
    /**
     * The name of the database table.
     *
     * @var string
     */
    protected string $table;

    /**
     * The column name to check uniqueness for.
     *
     * @var string
     */
    protected string $column;

    /**
     * Optional ID to ignore (e.g., when updating an existing record).
     *
     * @var int|null
     */
    protected ?int $ignoreId;

    /**
     * The column name for the ID (defaults to 'id').
     *
     * @var string
     */
    protected string $idColumn;

    /**
     * Create a new validation rule instance.
     *
     * @param string $table     The table to check.
     * @param string $column    The column to check for uniqueness (optional).
     * @param int|null $ignoreId   ID to ignore during the check (optional).
     * @param string $idColumn  The column name of the ID (default is 'id').
     */
    public function __construct(string $table, string $column = 'NULL', ?int $ignoreId = null, string $idColumn = 'id')
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->idColumn = $idColumn;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute The attribute name being validated.
     * @param mixed $value      The value to validate.
     * @return bool             True if the value passes uniqueness check.
     */
    public function passes($attribute, $value): bool
    {
        // Use Laravel's unique rule with additional condition: where deleted_at is NULL
        $rule = Rule::unique($this->table, $this->column === 'NULL' ? $attribute : $this->column)
                    ->whereNull('deleted_at');

        // If we're updating an existing record, ignore it in the check
        if ($this->ignoreId !== null) {
            $rule->ignore($this->ignoreId, $this->idColumn);
        }

        // Use Laravel's Validator to apply the rule
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => [$rule]]
        );

        // Return whether the validation passed
        return !$validator->fails();
    }

    /**
     * Get the validation error message.
     *
     * @return string The error message when validation fails.
     */
    public function message(): string
    {
        return 'The :attribute has already been taken.';
    }
}
