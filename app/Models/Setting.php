<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * 
 * @property string key
 * @property string value
 * @property string type
 * @property string group
 * 
 * @property boolean is_active
 *
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read
 * 
 * Methods
 * @method static SettingBuilder query()
 * @method SettingBuilder newEloquentBuilder($query)
 *
 * Configuration & Metadata
 * @property array $appends                   List of accessors to append to model's array form.
 * @property array $eagerLoading              List of relations to eager load dynamically.
 * @property static array $excludedFields     Fields not requiring translation during insert.
 * @property static array $translationFields  Translatable fields used in validation.
 * @property static array $columnsSearch      Fields used for search functionality.
 * @property static array $columnsToExport    Fields exported to Excel or other formats.
 * @property array $casts                     Attribute casting definitions (e.g., enums, dates).
 **/
class Setting extends BaseModel
{
    use SoftDeletes;
    //

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

        'key',
        'value',
        'type',
        'group',

        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = [];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = [];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = [];

    //field to use in dynamicTranslationRules to validation nullable or required in fields translations
    protected static $requiredFields = [];

    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = [];
    
    // fields for restore && translations
    public static $uniqueFields = [];
    
    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [

    ];
 
    
    /** Relations */


    /** Accessors */

    /** Methods */

    /**
     * @return SettingBuilder
     */
    public static function query(): SettingBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return SettingBuilder
     */
    public function newEloquentBuilder($query): SettingBuilder
    {
        return new SettingBuilder($query, $this);
    }
}
