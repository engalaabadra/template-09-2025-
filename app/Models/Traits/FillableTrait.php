<?php
namespace App\Models\Traits;

trait FillableTrait{

    /**
     * Filter a given data array by the model's $fillable.
     *
     * Useful in requests before storing/updating:
     * Banner::onlyFillable($request->validated());
     *
     * @param array $data
     * @return array
     */
    public static function onlyFillable(array $data): array
    {
        $fillable = (new static)->getFillable();
        return array_intersect_key($data, array_flip($fillable));
    }
}