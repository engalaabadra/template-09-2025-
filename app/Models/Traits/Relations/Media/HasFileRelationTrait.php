<?php
namespace App\Models\Traits\Relations\Media;

use App\Models\File;
use App\Models\Traits\HasMediaTrait;


trait HasFileRelationTrait{
    use HasMediaTrait;

    /**
     * Get the associated single file for the model.
     *
     * Defines a one-to-one polymorphic relationship with the File model,
     * filtered to include only records where the `type` column is 'file'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function file(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(File::class, 'fileable')->where('type', 'file');
    }

}