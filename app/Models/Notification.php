<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\Builders\NotificationBuilder;
 

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
 * @method static NotificationBuilder query()
 * @method NotificationBuilder newEloquentBuilder($query)
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

class Notification extends BaseModel
{
    use  SoftDeletes;
        
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
        'body',
        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['user.profile','file'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = [];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = [];

    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [
        'is_active' => \App\Enums\IsActiveEnum::class,
    ];

    /** Accessors */

    /** Methods */
      /**
     * @return NotificationBuilder
     */
    public static function query(): NotificationBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return NotificationBuilder
     */
    public function newEloquentBuilder($query): NotificationBuilder
    {
        return new NotificationBuilder($query, $this);
    }
}
