<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Rule as ValidationRule;

/**
 * Custom validation rule to ensure a value is unique
 * only among active and not-deleted records.
 */
class UniqueActiveAndNotDeleted implements Rule
{
    protected string $table;        // Table name
    protected string $column;       // Column to check uniqueness on
    protected ?int $ignoreId;       // Record ID to ignore (useful in updates)
    protected string $activeColumn; // Column indicating active state
    protected string $deletedColumn;// Column indicating soft delete

    /**
     * Initialize the rule.
     */
    public function __construct(
        string $table,
        string $column,
        ?int $ignoreId = null,
        string $activeColumn = 'is_active',
        string $deletedColumn = 'deleted_at'
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->activeColumn = $activeColumn;
        $this->deletedColumn = $deletedColumn;
    }

    /**
     * Build the unique validation rule.
     */
    public function rule(): Unique
    {
        // Create base unique rule
        $rule = ValidationRule::unique($this->table, $this->column);

        // Ignore a specific record if needed (e.g., update case)
        if ($this->ignoreId) {
            $rule->ignore($this->ignoreId);
        }

        // Apply conditions: only active and not deleted
        $rule->whereNull($this->deletedColumn)// Ensure uniqueness on non-deleted records (ignore items in trash)
             ->where($this->activeColumn, \App\Enums\IsActiveEnum::ACTIVE->value);//  Ensure uniqueness on activate records (ignore items not active)

        return $rule;
    }

    /**
     * Check if the validation passes.
     */
    public function passes($attribute, $value): bool
    {
        // Run validation with the custom unique rule
        return validator(
            [$attribute => $value],
            [$attribute => $this->rule()]
        )->passes();
    }

    /**
     * Error message when validation fails.
     */
    public function message(): string
    {
        return 'The :attribute has already been taken.';
    }
}
