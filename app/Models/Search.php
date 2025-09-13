<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Builders\SearchBuilder;

/**
 * Attributes
 * @property int id
 * @property int user_id
 * @property int session_id
 * @property string query
 * @property string searchable_id
 * @property string searchable_type ((User)Author, Content)
 * @property int results_count
 * @property boolean is_active
 *
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read mixed $searchable
 * 
 * Methods
 * @method static SearchBuilder query()
 * @method SearchBuilder newEloquentBuilder($query)
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
class Search extends BaseModel
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
        'user_id',
        'session_id',
        'query',
        'results_count',
        'searchable_id',
        'searchable_type', //((User)Author, Content)
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

    /**
     * Polymorphic relation to the parent model.
     *
     * Allows this search to belong to different models
     * (e.g. User(Author), Content,  etc.)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function searchable()
    {
        return $this->morphTo();
    }
    /** Accessors */

    /** Methods */

    /**
     * @return SearchBuilder
     */
    public static function query(): SearchBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return SearchBuilder
     */
    public function newEloquentBuilder($query): SearchBuilder
    {
        return new SearchBuilder($query, $this);
    }
}
