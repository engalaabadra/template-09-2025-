<?php
namespace App\Models\Traits\Relations\Media;

use App\Models\File;
use App\Models\Traits\HasMediaTrait;

trait HasImagesRelationTrait{
    use HasMediaTrait;
   
    /**
     * Get all associated images for the model.
     *
     * Defines a one-to-many polymorphic relationship with the File model,
     * filtered to include only records where the `type` column is 'image'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id')
                    ->where('type', 'image');
    }

}