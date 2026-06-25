<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Builders\RegisterCodeNumBuilder;

/**
 * Attributes
 * @property int id
 * @property string email
 * @property string phone_no
 * @property int country_id
 * @property string code
 * 
 * Relations
 * @property-read
 * 
 * Methods
 * @method static RegisterCodeNumBuilder query()
 * @method RegisterCodeNumBuilder newEloquentBuilder($query) 
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
class RegisterCodeNum extends Model
{
    /** Configuration & Metadata */

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'phone_no',
        'country_id',
        'code'
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
    public static $columnsToExport = [];

    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [
        'is_active' => \App\Enums\IsActiveEnum::class,
    ];

    /** Relations */

    /** Methods */

    /**
     * @return RegisterCodeNumBuilder
     */
    public static function query(): RegisterCodeNumBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return RegisterCodeNumBuilder
     */
    public function newEloquentBuilder($query): RegisterCodeNumBuilder
    {
        return new RegisterCodeNumBuilder($query, $this);
    }
}
