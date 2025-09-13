<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;
use App\Scopes\LanguageScope;
use App\Models\Traits\BaseModelTrait;
use App\Models\User;
use App\Enums\IsActiveEnum;
use App\GeneralClasses\MediaClass;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

/**
 * Attributes
 * @property-read User $createdBy
 * @property-read User $updatedBy
 * @property-read User $deletedBy
 * @property-read int $created_by_id
 * @property-read string $created_by_type
 * @property-read int $updated_by_id
 * @property-read string $updated_by_type
 * @property-read int $deleted_by_id
 * @property-read string $deleted_by_type
 * 
 *  Accessors
 * @property-read string|null $is_active_text
 *
 * Relations
 * @property-read
 * 
 * Methods
 * @method
 * 
 * Configuration & Metadata
 * @property array $appends                   List of accessors to append to model's array form.
 * @property array $eagerLoading              List of relations to eager load dynamically.
 * @property static array $excludedFields     Fields not requiring translation during insert.
 * @property static array $translationFields  Translatable fields used in validation.
 * @property static array $columnsSearch      Fields used for search functionality.
 * @property static array $columnsToExport    Fields exported to Excel or other formats.
 * @property array $casts                     Attribute casting definitions (e.g., enums, dates).
 */

class BaseModel extends Model
{
    use BaseModelTrait;

    
    /** Configuration & Metadata */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        
        'created_by_id', 'created_by_type', 'updated_by_id', 'updated_by_type', 'deleted_by_id', 'deleted_by_type',
    ];

    // Accessors that should be appended to the model's array and JSON form
    // protected $appends = ['is_active_text'];
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

    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = [];

    // fields for restore && translations
    public static $uniqueFields = [];
       
    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [
        'is_active' => \App\Enums\IsActiveEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


//    protected function applyAutoCasts(): void
//     {
//         $this->casts = array_merge(
//             $this->casts ?? [],
//             [
//                 'is_active' => \App\Enums\IsActiveEnum::class,
//                 'created_at' => 'datetime',
//                 'updated_at' => 'datetime',
//                 'deleted_at' => 'datetime',
//             ]
//         );
//     }

//     public function __construct(array $attributes = [])
//     {
//         parent::__construct($attributes);
//         $this->applyAutoCasts();
//     }
    /**
     * Boot the model and define model event listeners.
     *
     * This method sets up a listener for the "deleting" event.
     * If the model is being force deleted (i.e., not soft deleted),
     * it loops through the defined `forceCascadeDelete` relationships on the model
     * and deletes the related models accordingly.
     */
    // protected static function booted(): void
    // {

    //     Cache::put('supported_languages', config('app.supported_languages'));

    //     // Register a deleting event handler for the model
    //     static::deleting(function (Model $model) {

    //         // Check if the model uses SoftDeletes
    //         $usesSoftDeletes = isSoftDeletes($model);

    //         // If the model does NOT use SoftDeletes → any deletion is permanent (will use this : handleForceCascadeDelete($model)) to delete all relations its
    //         // Or if it uses SoftDeletes and is being force deleted
    //         if (!$usesSoftDeletes || ($usesSoftDeletes && $model->isForceDeleting())) {
    //             // Execute permanent deletion of its relationships
    //             $model->handleForceCascadeDelete($model);
    //         }

    //     });
    // }

}
