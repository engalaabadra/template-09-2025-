<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\Traits\Relations\TranslationRelationsTrait;
use App\Models\Builders\BoardBuilder;
use App\Models\Traits\HasMediaTrait;
use App\Models\Traits\Relations\Media\HasImageRelationTrait;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * @property string title
 * @property string url
 * @property string description
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read
 * 
 * Methods
 * @method static BoradBuilder query()
 * @method BoradBuilder newEloquentBuilder($query)
 * 
 * Configuration & Metadata
 * @property array $appends                   List of accessors to append to model's array form.
 * @property array $eagerLoading              List of relations to eager load dynamically.
 * @property static array $excludedFields     Fields not requiring translation during insert.
 * @property static array $translationFields  Translatable fields used in validation.
 * @property static array $columnsSearch      Fields used for search functionality.
 * @property static array $columnsToExport    Fields exported to Excel or other formats.
 * @property array $casts                     Attribute casting definitions (e.g., enums, dates).
 * 
 **/

class Board extends BaseModel
{
    use  HasMediaTrait, HasImageRelationTrait, TranslationRelationsTrait, SoftDeletes;

    protected $fillable = [
        'id',
        'lang',
        'translate_id',
        'description',
        'is_active'
    ];
     // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['image'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = []; 

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = ['description'];

    //field to use in dynamicTranslationRules to validation nullable or required in fields translations
    public static $requiredFields = ['description'];
    
    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['description'];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['description'];
    
     // Relations to be force deleted with the model
    protected array $forceCascadeDelete = ['image', 'translations'];
    
    // fields for restore && translations
    public static $uniqueFields = [];
    
    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [
        'is_active' => \App\Enums\IsActiveEnum::class,
    ];

    /** Accessors */


    /** Relations */

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

    /** Methods */
      
    /**
     * @return BoardBuilder
     */
    public static function query(): BoardBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return BoardBuilder
     */
    public function newEloquentBuilder($query): BoardBuilder
    {
        return new BoardBuilder($query, $this);
    }
}

