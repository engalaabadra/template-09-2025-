<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Traits\Relations\TranslationRelationsTrait;
use App\Models\Builders\BannerBuilder;
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
 * @method static BannerBuilder query()
 * @method BannerBuilder newEloquentBuilder($query)
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

class Banner extends BaseModel
{
    use  SoftDeletes, HasImageRelationTrait, TranslationRelationsTrait;

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
        'title',
        'url',
        'description',
        'is_active',

    ];

    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['translations', 'image'];
   // public static array $eagerLoading = ['translations','images'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = ['url'];
    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = ['title', 'description'];

    //field to use in dynamicTranslationRules to validation nullable or required in fields translations
    public static $requiredFields = ['title'];
    
    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['title', 'description', 'url', 'created_at'];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['title', 'description', 'url'];
    
    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = [ 'image', 'translations'];

    // fields for restore && translations
    public static $uniqueFields = ['title'];
   
    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [

    ];

    /** Accessors */


    /** Relations */

    /** Methods */


    /**
     * @return BannerBuilder
     */
    public static function query(): BannerBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return BannerBuilder
     */
    public function newEloquentBuilder($query): BannerBuilder
    {
        return new BannerBuilder($query, $this);
    }

}
