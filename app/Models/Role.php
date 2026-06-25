<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use App\Models\BaseModel; // Assuming this extends Model
use App\Models\User;
use App\Models\Traits\Relations\TranslationRelationsTrait;
use App\Models\Builders\RoleBuilder;
use App\Services\ServiceResponse;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;
use App\Models\Traits\MainRolesHandling;

/**
 * Attributes
 * @property int id
 * @property string lang
 * @property int translate_id
 * @property string guard_name
 * @property string name
 * @property string display_name
 * @property boolean is_active
 * 
 * Accessors
 * @property-read string|null $_text
 *
 * Relations
 * @property-read User[] users
 * @property-read Permission[] Permissions
 *
 * Methods
 * @method static RoleBuilder query()
 * @method RoleBuilder newEloquentBuilder($query) 
 * @method bool rolesToCheck
 * 
 * Configuration & Metadata
 * @property array $appends                   List of accessors to append to model's array form.
 * @property array $eagerLoading              List of relations to eager load dynamically.
 * @property static array $excludedFields     Fields not requiring translation during insert.
 * @property static array $translationFields  Translatable fields used in validation.
 * @property static array $columnsSearch      Fields used for search functionality.
 * @property static array $columnsToExport    Fields exported to Excel or other formats.
 * @property array $casts                     Attribute casting definitions (e.g., enums, dates).
 * @property static array $mainRoles          Stores default/main role names (e.g., from config).
 * 
 **/
class Role extends BaseModel
{
    use SoftDeletes, MainRolesHandling, TranslationRelationsTrait;

    /** Configuration & Metadata */

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $fillable = [
        'id',
        "lang",
        "translate_id",
        'guard_name',
        'name',
        'display_name',
        'is_active'
    ];

    // Accessors that should be appended to the model's array and JSON form
    protected $appends = [];

    // List of relationships to eager load dynamically when needed
    public static array $eagerLoading = ['permissions'];

    // Fields that are excluded from translation when inserting a new record
    public static $excludedFields = [];

    // Fields that are translatable; used for adding dynamic validation rules for translations in form requests
    public static $translationFields = ['guard_name', 'name', 'display_name'];

    //field to use in dynamicTranslationRules to validation nullable or required in fields translations
    public static $requiredFields = ['name', 'display_name'];
    
    // Fields used for search functionality (e.g., in filtering, search bars, etc.)
    public static $columnsSearch = ['guard_name', 'name', 'display_name'];

    // Fields to include when exporting model data (e.g., to Excel or CSV)
    public static $columnsToExport = ['guard_name', 'name', 'display_name'];

    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = ['users', 'permissions', 'translations'];
    
    public static $mainRolesNames = [];
    public static $mainRolesIds = [];

    
    // fields for restore && translations
    public static $uniqueFields = ['name', 'display_name'];
    
    // Cast definitions for model attributes (e.g., enum, date, boolean, etc.)
    protected $casts = [

    ];

    /** Relations */

    /**
     * Get the users that are assigned to this role.
     *
     * Defines a many-to-many relationship using the `model_has_roles` pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
    }

    /**
     * Get the permissions that belong to this role.
     *
     * Defines a many-to-many relationship using the `role_has_permissions` pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    /** Methods */

    /**
     * Get report configuration for a specific model and report type.
     *
     * @param mixed $model The model instance or class name for which the report is being generated.
     * @param string $type The type of report required (e.g., 'by_active', 'by_day', 'by_role').
     * @return array The configuration for the requested report type.
     */
    public static function getReportConfig($model, string $type): array
    {
        // Generate the count alias based on the model name (e.g., 'user_count')
        $model_count = modelName($model) . '_count';

        // Get the shared report configurations (like 'by_active', 'by_day')
        $commonReports = static::getCommonReports($model_count);

        // Custom configuration for report type: 'by_role'
        if ($type === 'users_count_per_role') {
            return [
                'raw' => 'roles.name as role_name, COUNT(users.id) as users_count',
                'from' => 'roles',
                'join' => [
                ['model_has_roles', 'roles.id', '=', 'model_has_roles.role_id'],
                ['users', 'users.id', '=', 'model_has_roles.model_id'],
                ],
                'where' => [
                    ['model_has_roles.model_type', '=', \App\Models\User::class],
                ],
                'groupBy' => ['roles.name'],
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


    /**
     * @return RoleBuilder
     */
    public static function query(): RoleBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return RoleBuilder
     */
    public function newEloquentBuilder($query): RoleBuilder
    {
        return new RoleBuilder($query, $this);
    }

}
