<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * @property string fileable_id
 * @property string fileable_type
 * @property string url
 * @property string type
 * @property boolean is_active
 * 
 * Relations
 * @property-read mixed $fileable
 * 
 * Configuration & Metadata
 * @property array $casts                     Attribute casting definitions (e.g., enums, dates).
 * 
 **/

class File extends BaseModel
{
        
    /** Configuration & Metadata */

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'lang',
        'translate_id',
        'fileable_id',
        'fileable_type',
        'url',
        'type',
        'is_active'
    ];

    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [

    ];

    /** Relations */

    /**
     * Polymorphic relation to the parent model.
     *
     * Allows this file to belong to different models
     * (e.g. User, Banner, etc.)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function fileable()
    {
        return $this->morphTo();
    }


}
