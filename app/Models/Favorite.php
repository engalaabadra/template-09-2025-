<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\ActiveScope;
use App\Models\Builders\FavoriteBuilder;
use App\Models\BaseModel;
use App\Models\Traits\OwnedByUserLocalScopeTrait;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * @property int user_id
 * @property int content_id
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read User user
 * @property-read Content content
 * 
 * Methods
 * @method static FavoriteBuilder query()
 * @method FavoriteBuilder newEloquentBuilder($query)
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

class Favorite extends BaseModel
{
    use OwnedByUserLocalScopeTrait;//use it , When Want (find data for this user only) to apply OwnedByUser local scope
        
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
        'content_id',
        'user_id',
        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['user.profile', 'content.user.profile'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['user.profile.username'];//fields for search

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['user.profile.username'];

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
     * Get the content that owns this model.
     *
     * Defines an inverse one-to-many relationship to the content model using the `content_id` foreign key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Content::class);
    }


    /** Accessors */


    /** Methods */

      /**
     * @return FavoriteBuilder
     */
    public static function query(): FavoriteBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return FavoriteBuilder
     */
    public function newEloquentBuilder($query): FavoriteBuilder
    {
        return new FavoriteBuilder($query, $this);
    }
}
