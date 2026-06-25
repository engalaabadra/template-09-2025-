<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\Traits\Relations\TranslationRelationsTrait;
use App\Models\Builders\ContactBuilder;
use App\Models\Traits\Relations\Media\HasFilesRelationTrait;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * @property int user_id
 * @property string name
 * @property string email
 * @property string phone_no
 * @property string message
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read
 * 
 * Methods
 * @method static ContactBuilder query()
 * @method ContactBuilder newEloquentBuilder($query)
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

class Contact extends BaseModel
{
    use  HasFilesRelationTrait, SoftDeletes;

        
    /** Configuration & Metadata */

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        //'user_id',
        'name',
        'email',
        'phone_no',
        'message',
        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['files'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['name', 'message', 'phone_no', 'email'];//fields for search

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['name', 'message', 'phone_no', 'email'];

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
     * @return ContactBuilder
     */
    public static function query(): ContactBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return ContactBuilder
     */
    public function newEloquentBuilder($query): ContactBuilder
    {
        return new ContactBuilder($query, $this);
    }

}
