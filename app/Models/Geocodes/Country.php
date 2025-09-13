<?php

namespace App\Models\Geocodes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\User; 
use App\Models\Builders\CountryBuilder;
use App\Models\Traits\Relations\TranslationRelationsTrait;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * @property string name
 * @property string flag
 * @property string code
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

class Country extends BaseModel 
{
    use  TranslationRelationsTrait, SoftDeletes;

    /** Configuration & Metadata */
    
     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $fillable = [
        'id',
        'lang',
        'translate_id',
        'name',
        'flag',
        'code',
        'code2',
        'numcode',
        'phone_code',
        'is_active'
    ];

       // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['user.profile', 'translations'];
   // public static array $eagerLoading = ['translations','images'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = ['flag', 'code', 'code2', 'numcode', 'phone_code'];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = ['name']; // these fields to use it in request file to Add dynamic validation rules for each field in the translations in request

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['name', 'flag', 'code', 'code2', 'numcode', 'phone_code'];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['id', 'name', 'flag', 'code', 'code2', 'numcode', 'phone_code'];
    
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

    /** Methods */

     /**
     * @return CountryBuilder
     */
    public static function query(): CountryBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return CountryBuilder
     */
    public function newEloquentBuilder($query): CountryBuilder
    {
        return new CountryBuilder($query, $this);
    }
}
