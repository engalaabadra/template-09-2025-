<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\Builders\ChatBuilder;
use App\Models\Traits\Relations\Media\HasFilesRelationTrait;
use App\Models\Traits\Relations\Media\HasFileRelationTrait;
use App\Models\Traits\OwnedByUserLocalScopeTrait;


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
 * @property-read User user
 * @property-read User client
 * 
 * Methods
 * @method static ChatBuilder query()
 * @method ChatBuilder newEloquentBuilder($query)
 * 
 * Configuration & Metadata
 * @property array $appends                   List of accessors to append to model's array form.
 * @property array $eagerLoading              List of relations to eager load dynamically.
 * @property static array $excludedFields     Fields not requiring translation during insert.
 * @property static array $columnsSearch      Fields used for search functionality.
 * @property static array $columnsToExport    Fields exported to Excel or other formats.
 * @property array $casts                     Attribute casting definitions (e.g., enums, dates).
 * 
 **/

class Chat extends BaseModel
{
    use  HasFilesRelationTrait, HasFileRelationTrait, SoftDeletes;
    use OwnedByUserLocalScopeTrait;//use it , When Want (find data for this user only) to apply OwnedByUser local scope

        
    /** Configuration & Metadata */

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'client_id',
        'body',
        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['client.profile','user.profile', 'files', 'file'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['user.profile.username', 'body'];//fields for search

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['user.profile.username', 'body'];

    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = ['files'];

      // fields for restore && translations
    public static $uniqueFields = [];

    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [

    ];


    /** Accessors */


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
     * Get the client that owns this model.
     *
     * Defines an inverse one-to-many relationship to the User model using the `user_id` foreign key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** Methods */  

    /**
     * @return ChatBuilder
     */
    public static function query(): ChatBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return ChatBuilder
     */
    public function newEloquentBuilder($query): ChatBuilder
    {
        return new ChatBuilder($query, $this);
    }
}
