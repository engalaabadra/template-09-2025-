<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\Builders\ReviewBuilder;
use App\Models\Traits\Relations\Media\HasFilesRelationTrait;

/**
 * Attributes
 * @property int id
 * 
 * @property int user_id
 * @property int reviewable_id
 * @property string reviewable_type ((User)Author, Content)
 * @property string body
 * @property float rating
 * 
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read mixed $reviewable
 * 
 * Methods
 * @method static ReviewBuilder query()
 * @method ReviewBuilder newEloquentBuilder($query) 
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
class Review extends BaseModel
{
    use  SoftDeletes, HasFilesRelationTrait;

    /** Configuration & Metadata */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'reviewable_id',
        'reviewable_type', // ((User)Author, Content)
        'rating',
        'description',
        'is_active'
    ];

    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['user.profile'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['user.profile.username', 'description', 'rating'];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['user.profile.username', 'description', 'rating'];

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
     * Allows this review to belong to different models
     * (e.g. User(author), Content,  etc.)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function reviewable()
    {
        return $this->morphTo();
    }

    /** Methods */

    /**
     * @return ReviewBuilder
     */
    public static function query(): ReviewBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return ReviewBuilder
     */
    public function newEloquentBuilder($query): ReviewBuilder
    {
        return new ReviewBuilder($query, $this);
    }
}
