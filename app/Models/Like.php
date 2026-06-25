<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Attributes
 * @property int id
 * @property int user_id
 * @property int likeable_id
 * @property string likeable_type  (Content, Comment, Reply)
 * @property boolean is_active
 *
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read mixed $likeable
 * 
 * Methods
 * @method static LikeBuilder query()
 * @method LikeBuilder newEloquentBuilder($query)
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
class Like extends BaseModel
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
        'likeable_id',
        'likeable_type',// (Content, Comment, Reply)
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
     * Get the user that owns this model.
     *
     * Defines an inverse one-to-many relationship to the User model using the `user_id` foreign key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
   
    /**
     * Polymorphic relation to the parent model.
     *
     * Allows this like to belong to different models
     * (e.g. Content, Comment, Reply  etc.)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function likeable()
    {
        return $this->morphTo();
    }

    /** Accessors */

    /** Methods */

    /**
     * @return LikeBuilder
     */
    public static function query(): LikeBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return LikeBuilder
     */
    public function newEloquentBuilder($query): LikeBuilder
    {
        return new LikeBuilder($query, $this);
    }
}
