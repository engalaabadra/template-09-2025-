<?php

namespace App\Models\Traits;

/**
 * Safely get/set class-level (static) config properties.
 *
 * Example:
 * User::setProp('columnsToExport', ['id','email']);
 * $cols = User::getProp('columnsToExport', []); // ['id','email']
 */
trait SafePropsTrait
{
    /**
     * Get a static property safely. Returns $default if it doesn't exist.
     *
     * @param string $name    Property name (must be a static property)
     * @param mixed  $default Default if not found (default: [])
     * @return mixed
     */
    public static function getProp(string $name, $default = [])
    {
        return property_exists(static::class, $name)
            ? static::$$name
            : $default;
    }

    /**
     * Set a static property safely (no-op if it doesn't exist).
     *
     * @param string $name  Property name (must be a static property)
     * @param mixed  $value Value to assign
     * @return void
     */
    public static function setProp(string $name, $value): void
    {
        if (property_exists(static::class, $name)) {
            static::$$name = $value;
        }
    }

    /** Backward-compat alias for older calls like User::staticProp('...') */
    public static function staticProp(string $name, $default = [])
    {
        return static::getProp($name, $default);
    }
}
