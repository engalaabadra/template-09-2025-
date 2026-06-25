<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Builders\ShelfBuilder;
use App\Models\Traits\Relations\TranslationRelationsTrait;
use App\Models\Traits\OwnedByUserLocalScopeTrait;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * @property int user_id
 * @property string title
 * @property string slug
 * @property string description
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
 * @method static ShelfBuilder query()
 * @method ShelfBuilder newEloquentBuilder($query)
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
class Shelf extends BaseModel
{
    use SoftDeletes, TranslationRelationsTrait,
    OwnedByUserLocalScopeTrait;//use it , When Want (find data for this user only) to apply OwnedByUser local scope


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
        'user_id',
        'title',
        'slug',
        'description',
        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
   // public static array $eagerLoading = ['translations', 'user.profile', 'contents'];
    public static array $eagerLoading = ['translations', 'contents'];

    #region ===================== Start translations properties(must fill it if this model need to translation) =====================

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = ['user_id'];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = ['title', 'slug', 'description'];

    //field to use in dynamicTranslationRules to validation nullable or required in fields translations
    public static $requiredFields = ['title', 'slug'];

    #endregion ===================== End translations properties =====================

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['title', 'slug'];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['title', 'slug', 'description'];

    

    // Relations to be force deleted with the model
    public array $forceCascadeDelete = [];
    
    // fields for restore && translations
    public static $uniqueFields = ['title', 'slug'];

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
        return $this->belongsTo(User::class, 'user_id');
    }
        
    /**
     * Get all contents related to this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Content::class);
    }

     public function translations()
    {
        // A record can have many translations
        return $this->hasMany(static::class, 'translate_id', 'id')->withoutGlobalScopes();
    }

    /** Accessors */

    /** Methods */
    

    /**
     * @return ShelfBuilder
     */
    public static function query(): ShelfBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return ShelfBuilder
     */
    public function newEloquentBuilder($query): ShelfBuilder
    {
        return new ShelfBuilder($query, $this);
    }
}
