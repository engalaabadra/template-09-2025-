<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\ActiveScope;
use App\Models\order;
use App\Models\Builders\OrderBuilder;
use App\Enums\OrderStatusEnum;
use App\Models\BaseModel;
use App\Models\Traits\Relations\Media\HasFilesRelationTrait;

/**
 * Attributes
 * @property int id
 * @property int order_id
 * @property int content_id
 * @property decimal price
 * @property decimal total
 * @property int quantity
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string $currency_user
 * 
 * Relations
 * @property-read Order order
 * @property-read Content contents
 * 
 * Methods
 * @method static OrrderBuilder query()
 * @method OrrderBuilder newEloquentBuilder($query) 
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

class OrderContent extends BaseModel
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
        'order_id',
        'content_id',
        'price',
        'quantity',
        'total',
        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = ['currency_user'];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['order', 'content'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['order.total', 'content.title', 'quantity', 'price', 'total'];//fields for search

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['order.total', 'content.title', 'quantity', 'price', 'total'];

    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = [];

    // fields for restore && translations
    public static $uniqueFields = [];


    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [

    ];

    /** Relations */
    
    /**
     * Get the order that owns this model.
     *
     * Defines an inverse one-to-many relationship to the order model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the content that this model belongs to.
     *
     * Defines an inverse one-to-many relationship to the content model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }


    /** Accessors */



    /**
     * Accessor to get the user's currency code.
     *
     * Returns a default currency code (hardcoded as 'USD').
     * This can be customized later to fetch dynamically from a related model or setting.
     *
     * @return string
     */
    public function getCurrencyUserAttribute(): string
    {
        return 'USD';
    }

    /** Methods */
    
    /**
     * @return OrderBuilder
     */
    public static function query(): OrderBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return OrderBuilder
     */
    public function newEloquentBuilder($query): OrderBuilder
    {
        return new OrderBuilder($query, $this);
    }
}
