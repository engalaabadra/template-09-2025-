<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ContentTypeEnum;
use App\Models\Traits\OwnedByUserLocalScopeTrait;
use App\Models\Builders\ContentBuilder;
use App\Models\Traits\Relations\Media\HasFilesRelationTrait;
use App\Models\Traits\Relations\TranslationRelationsTrait;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * 
 * @property int parent_content_id
 * @property int shelf_id
 * @property int category_id
 * @property string type
 * @property string title
 * @property string slug
 * @property string description
 * @property string content_text
 * @property string summery
 * @property string chapters
 * @property boolean is_featured
 * @property int reads_count
 * @property int searches_count
 * @property int likes_count
 * @property date published_at
 * 
 * @property boolean is_active
 *
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read Shelf shelf
 * @property-read Order orders
 * 
 * Methods
 * @method static ContentBuilder query()
 * @method ContentBuilder newEloquentBuilder($query)
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
class Content extends BaseModel
{

    use SoftDeletes, HasFilesRelationTrait, TranslationRelationsTrait,
    
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

        'parent_content_id',
        'shelf_id',
        'user_id',
        'category_id',
        'type', // Content , Article
        'title',
        'slug',
        'description',
        'content_text',
        'price',
        'summery',
        'chapters',
        'is_featured',
        'reads_count',
        'searches_count',
        'likes_count',
        'reviews_count',
        'published_at',

        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = [
        'translations',
        'files',
        'user.profile',
        'shelf',
        'category'
    //    'user.reviewsAuthor.user',                 // The book's author , with  Reviews written for the author with user details
      //  'likesContent.user',             // Likes on the book with user details
        // 'comments.replies.user',  // Comments + their replies + user details
        // 'comments.likes.user',    // Likes on comments with user details
        // 'comments.user',          // The user who wrote each comment
        // 'reviewsConent.user',       // Reviews written for the book with user details
    ];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = ['user_id', 'shelf_id', 'category_id', 'type'];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = ['title', 'slug'];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['title', 'slug'];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['title', 'slug'];

    //field to use in dynamicTranslationRules to validation nullable or required in fields translations
    //public static $requiredFields = ['title', 'slug'];
    public static $requiredFields = ['title', 'slug'];

    // Relations to be force deleted with the model
    public array $forceCascadeDelete = [];
    
    // fields for restore && translations
    public static $uniqueFields = ['title', 'slug'];

    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [
        'type' => ContentTypeEnum::class,
        'published_at' => 'datetime',

    ];
    
    /** Relations */

    /**
     * Get the Orders that this model belongs to many.
     *
     * Defines an inverse many-to-many relationship to the Order model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }

    /**
     * Get the category that this content belongs to.
     *
     * Defines an inverse one-to-many relationship where
     * each content is associated with a single category,
     * while a category can have multiple contents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    /**
     * Get the user that owns this model.
     *
     * Defines an inverse one-to-many relationship to the Shelf model using the `user_id` foreign key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shelf that owns this model.
     *
     * Defines an inverse one-to-many relationship to the Shelf model using the `shelf_id` foreign key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shelf() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    /**
     * Get all reviews related to this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all comments related to this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all searches for the content.
     *
     * Polymorphic one-to-many relation:
     * A content can have many searches.
     */
    public function searchesContent()
    {
        return $this->morphMany(Search::class, 'searchable');
    }

    /**
     * Get all likes for the content.
     *
     * Polymorphic one-to-many relation:
     * A content can have many likes.
     */
    public function likesContent()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Get all reviews for the content.
     *
     * Polymorphic one-to-many relation:
     * A content can have many reviews.
     */
    public function reviewsContent()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_saved_contents');
    }
    
    /** Methods */


    /** Accessors */

    /** Methods */

    /**
     * @return ContentBuilder
     */
    public static function query(): ContentBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return ContentBuilder
     */
    public function newEloquentBuilder($query): ContentBuilder
    {
        return new ContentBuilder($query, $this);
    }
}
