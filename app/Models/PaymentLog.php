<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\Builders\PaymentLogBuilder;
 
/**
 * Attributes
 * @property int id
 * @property string transaction_id
 * @property string customer_id
 * @property string source
 * @property string status
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read
 * 
 * Methods
 * @method static PaymentLogBuilder query()
 * @method PaymentLogBuilder newEloquentBuilder($query)
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

class PaymentLog extends BaseModel
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
        'transaction_id',
        'customer_id',
        'source',
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
    public static $columnsToExport = ['id', 'created_at'];

    // fields for restore && translations
    public static $uniqueFields = [];


    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [

    ];

      
    /**
     * @return PaymentLogBuilder
     */
    public static function query(): PaymentLogBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return PaymentLogBuilder
     */
    public function newEloquentBuilder($query): PaymentLogBuilder
    {
        return new PaymentLogBuilder($query, $this);
    }
}
