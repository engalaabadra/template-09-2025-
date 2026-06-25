<?php

namespace App\Models\Traits\Accessors;

use DateTimeInterface;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
/**
 * Trait SmartAttributesTrait
 *
 * - Auto applies casts defined in `$autoCasts`-> applyAutoCasts()
 * - Dynamically generates *_text attributes (all casts) for enums & datetimes (via toArray() only).
 * - when get these casts attr., will calling auto. getAttribute() to rendering these casts & this method make formatting these casts enums, datetimesto rendering with this formatting
 * - getSmartAttributes() -> rendering (fillable + *_text + relations -> Supports recursive relation handling (with Resource mapping).).
 *
 * @mixin Model
 */
trait SmartAttributesTrait
{
    /**
     * Custom-defined casts to be applied automatically.
     *
     * Keys: database column names
     * Values: cast type or Enum class
     * 
     * @var array<string, string>
     */
    protected array $autoCasts = [
        'is_active' => \App\Enums\IsActiveEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

     /**
     * Boot method automatically called by Laravel when trait is used.
     * Hooks into model lifecycle events to apply auto casts on retrieval, creation, and updates.
     */
    public static function bootSmartAttributesTrait(): void
    {
        static::retrieved(fn($model) => $model->applyAutoCasts());
        static::creating(fn($model) => $model->applyAutoCasts());
        static::updating(fn($model) => $model->applyAutoCasts());
    }

    
    /**
     * Apply automatic casts by merging $casts (defined in model) with $autoCasts.
     * Only applies casts for columns that exist in the database table.
     */
    protected function initializeSmartAttributesTrait(): void
    {
        $this->applyAutoCasts();
    }

    /**
     * Apply auto casts by merging `$casts` and `$autoCasts`.
     */
    protected function applyAutoCasts(): void
    {
        $allCasts = array_merge($this->casts ?? [], $this->autoCasts);
        foreach ($allCasts as $column => $cast) {
            if ($this->hasColumn($column)) {
                $this->casts[$column] = $cast;
            }
        }
    }

    /**
     * Convert model to array with support for *_text attributes and filtering.
     * Ensures that hidden columns are skipped.
     *
     * @return array
     */
    public function toArray(): array
    {
        $this->applyAutoCasts();

        $array = parent::toArray();

        foreach ($this->casts ?? [] as $column => $cast) {
            if (!$this->hasColumn($column)) {
                continue;// Skip non-existent columns
            }
        
            if (in_array($column, $this->hidden ?? [])) {
                continue; // Skip hidden columns
            }

            // Add dynamic *_text representation
            $array[$column . '_text'] = $this->getAttribute($column . '_text');
        }

        // Filtering logic (optional, can remove attributes)
        return $array;
    }

    /**
     * Override getAttribute to resolve *_text attributes dynamically.
     * 
     * * - Enums
     * - DateTimeInterface values
     * - Objects implementing HasTextRepresentation
     *
     * Returns null if column does not exist or is not casted.
     */
    public function getAttribute($key)
    {
        if (str_ends_with($key, '_text')) {
            $baseKey = str_replace('_text', '', $key);

            if (!array_key_exists($baseKey, $this->casts ?? []) || !$this->hasColumn($baseKey)) {
                return null;
            }

            $value = parent::getAttribute($baseKey);

            if ($value instanceof DateTimeInterface) {
                return $this->formatFullDateTime($value);
            }

            if ($value instanceof BackedEnum || $value instanceof \App\Contracts\HasTextRepresentation) {
                return method_exists($value, 'text')
                    ? $value->text()
                    : $value->name;
            }

            return $value !== null ? (string)$value : null;
        }

        return parent::getAttribute($key);
    }

    

    /**
     * Get smart attributes (fillable + *_text + relations-> (recursive, can map to Resource classes)).
     *
     * @param array $relationsResources
     * @return array
     */
    public function getSmartAttributes(array $relationsResources = []): array
    {
        $data = $this->toArray();// in this method get attr. _text that is calling getAttributes to rendering it , getAttributes-> this contain formtting all attr. _text

        // Keep only id + fillable
        $allowed = array_merge(['id'], $this->getFillable());
        $data = array_intersect_key($data, array_flip($allowed + array_keys($data)));

        // Handle relations recursively
        foreach ($this->getRelations() as $relation => $value) {
            $resourceClass = $relationsResources[$relation] ?? null;

            if ($resourceClass) {
                if ($value instanceof Model) {
                    $data[$relation] = new $resourceClass($value);
                } elseif ($value instanceof \Illuminate\Support\Collection) {
                    $data[$relation] = $resourceClass::collection($value);
                }
            } else {
                if ($value instanceof Model && !($value instanceof \Illuminate\Database\Eloquent\Relations\Pivot)) {
                    $data[$relation] = $value->getSmartAttributes($relationsResources);
                } elseif ($value instanceof \Illuminate\Support\Collection) {
                    $data[$relation] = $value->map(
                        fn($item) =>
                            $item instanceof Model && !($item instanceof \Illuminate\Database\Eloquent\Relations\Pivot)
                                ? $item->getSmartAttributes($relationsResources)
                                : (array) $item
                    )->toArray();
                } else {
                    $data[$relation] = $value;
                }
            }
        }

        return $data;
    }

    

    /**
     * Check if a column exists in the database table.
     * Uses a static cache per table to reduce repeated schema lookups.
     *
     * @param string $column
     * @return bool
     */
    protected function hasColumn(string $column): bool
    {
        static $columnsCache = [];
        $table = $this->getTable();

        if (!isset($columnsCache[$table])) {
            $columnsCache[$table] = \Schema::getColumnListing($table);
        }

        return in_array($column, $columnsCache[$table], true);
    }

    /**
     * Format DateTime value to a simple human-readable string.
     * Examples:
     * - "10 minutes ago"
     * - "2025-08-26 10:30 AM"
     * - "2025-08-26"
     *
     * @param  \DateTimeInterface|string|null  $value
     *         The date/time value (can be Carbon, DateTime, or raw string).
     *
     * @return string|null
     *         Formatted date/time string, or null if input is empty.
     */
    protected function formatDateTime($date): ?string
    {
        if (!$date) return '---';
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        if ($carbon->diffInHours() < 24) {
            return self::translateDate($carbon->diffForHumans());
        }

        if ($carbon->diffInDays() < 7) {
            return self::translateDate($carbon->format('Y-m-d h:i A'));
        }

        return self::translateDate($carbon->format('Y-m-d'));
    }

     /**
     * Format a given DateTime value to a full date/time string with seconds.
     *
     * Example output:
     * - "2025-08-26 10:30:45 AM"
     *
     * @param  \DateTimeInterface|string|null  $value
     *         The date/time value (can be Carbon, DateTime, or raw string).
     *
     * @return string|null
     *         Fully formatted date/time string, or null if input is empty.
     */
    
    protected function formatFullDateTime($date): ?string
    {
        if (!$date) return '---';
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return self::translateDate($carbon->format('Y-m-d h:i A'));
    }

    /**
     * Translate AM/PM markers in a formatted date string 
     * based on the current application locale.
     *
     * - For Arabic (`ar`): "AM" → "ص", "PM" → "م".
     * - For English (`en`): "AM" → "AM", "PM" → "PM".
     * - Defaults to English replacements if the locale is not supported.
     *
     * @param string $formattedDateTime The formatted date string (e.g., "02:30 PM").
     * @return string The date string with localized AM/PM markers.
     */
    protected static function translateDate(string $formattedDateTime): string
    {
        $locale = app()->getLocale();

        $replacements = [
            'ar' => ['AM' => 'ص', 'PM' => 'م'],
            'en' => ['AM' => 'AM', 'PM' => 'PM'],
        ];

        $replacement = $replacements[$locale] ?? $replacements['en'];

        return str_replace(array_keys($replacement), array_values($replacement), $formattedDateTime);
        
    }
}
