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
 * @property int user_id
 * @property int payment_method_id
 * @property int content_id
 * @property float total
 * @property int quantity
 * @property OrderStatusEnum status
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string|null $status_text
 * @property-read string $currency_user
 * 
 * Relations
 * @property-read User user
 * @property-read PaymentMethod paymentMethod
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

class Order extends BaseModel
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
        'payment_method_id',
        'payment_method_status',
        'shipping_address',
        'billing_address',
        'total',
        'status',
        'notes',
        'is_active'
    ];
    // Accessors that should be appended to the model's array and JSON form
    protected $appends = ['currency_user'];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['user.profile', 'content', 'paymentMethod'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = [];

    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['user.profile.username', 'content.title','paymentMethod.name', 'total', 'status'];//fields for search

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['user.profile.username', 'content.title','paymentMethod.name','total', 'status'];

    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = [];

    // fields for restore && translations
    public static $uniqueFields = [];


    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [
        'status' => OrderStatusEnum::class,

    ];

    /** Relations */
    
    /**
     * Get the user that owns this model.
     *
     * Defines an inverse one-to-many relationship to the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the paymentMethod that this model belongs to.
     *
     * Defines an inverse one-to-many relationship to the PaymentMethod model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the Contents that this model belongs to many.
     *
     * Defines an inverse many-to-many relationship to the Content model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contents()
    {
        return $this->belongsToMany(Content::class);
    }


    /** Accessors */

    /**
     * Get the localized status text for the order.
     *
     * @return string|null
     */
    public function getStatusTextAttribute(): string|null
    {
        return $this->status?->text();
    }


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
    // public static function getReportConfig(string $type): array 
    // {
    //     return match ($type) {
            
    //         // Number of orders by status (e.g. new, completed, canceled, etc.)
    //         'by_status' => [
    //             'raw' => 'status, COUNT(id) as orders_count',
    //             'groupBy' => ['status'],
    //         ],

    //         // Number of orders by payment method (e.g. cash, card, bank transfer, etc.)
    //         'by_payment_method' => [
    //             'raw' => 'payment_method_id, COUNT(id) as orders_count',
    //             'groupBy' => ['payment_method_id'],
    //         ],

    //         // Number of orders by creation date (daily)
    //         'by_day' => [
    //             'raw' => 'DATE(created_at) as order_date, COUNT(id) as orders_count',
    //             'groupBy' => ['order_date'],
    //         ],
            
    //         // Total amount of orders grouped by status
    //         'total_amount_by_status' => [
    //             'raw' => 'status, SUM(total) as total_sum',
    //             'groupBy' => ['status'],
    //         ],

    //         // Default report configuration
    //         default => [
    //             'raw' => 'COUNT(id) as orders_count',
    //             'groupBy' => [],
    //         ],
    //     };
    // }



    public static function getReportConfig($model, string $type): array
    {
        // Generate the count alias based on the model name (e.g., 'user_count')
        $model_count = modelName($model) . '_count';

        // Get the shared report configurations (like 'by_active', 'by_day')
        $commonReports = $this->getCommonReports($model_count);

        // Custom configuration for report type: 'by_status'
        if ($type === 'by_status') {
            return [
                'raw' => 'status, COUNT(id) as orders_count',
                'groupBy' => ['status'],
            ];
        }
        if ($type === 'by_payment_method') {
            return [
                'raw' => 'payment_method_id, COUNT(id) as orders_count',
                'groupBy' => ['payment_method_id']
            ];
        }
        if ($type === 'total_amount_by_status') {
            return [
                'raw' => 'status, SUM(total) as total_sum',
                'groupBy' => ['status'],
            ];
        }

        // If the requested type exists in common reports, return it
        if (isset($commonReports[$type])) {
            return $commonReports[$type];
        }
        return [
            'raw' => 'COUNT(id) as ' . $model_count, // Raw SQL to count total records
            'groupBy' => [], // No grouping applied
        ];
    }




    // public static function getReportConfig(string $type): array
    // {
    //     $shared = static::getCommonReports('orders_count');

    //     return match ($type) {
    //         // Number of orders by status (e.g. new, completed, canceled, etc.)
    //         'by_status' => [
    //             'raw' => 'status, COUNT(id) as orders_count',
    //             'groupBy' => ['status'],
    //         ],

    //         // Number of orders by payment method (e.g. cash, card, bank transfer, etc.)
    //         'by_payment_method' => [
    //             'raw' => 'payment_method_id, COUNT(id) as orders_count',
    //             'groupBy' => ['payment_method_id'],
    //         ],
            
    //         // Total amount of orders grouped by status
    //         'total_amount_by_status' => [
    //             'raw' => 'status, SUM(total) as total_sum',
    //             'groupBy' => ['status'],
    //         ],

    //         // Default report configuration
    //         default => [
    //             'raw' => 'COUNT(id) as orders_count',
    //             'groupBy' => [],
    //         ],
  
            
    //     };
    // }

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
