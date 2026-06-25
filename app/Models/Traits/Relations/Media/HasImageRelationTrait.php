<?php
namespace App\Models\Traits\Relations\Media;

use App\Models\File;
use App\Models\Traits\HasMediaTrait;


trait HasImageRelationTrait{

    use HasMediaTrait;

    /**
     * Get the associated image file for the model.
     *
     * This defines a one-to-one polymorphic relation with the File model,
     * filtered to only include media of type 'image'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function image() : \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(File::class, 'fileable')->where('type', 'image');
    }
}
