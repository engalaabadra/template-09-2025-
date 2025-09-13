<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use App\Models\Role;
use App\Models\Permission;
use App\Scopes\ActiveScope;
use App\Models\Geocodes\Country;
use Laratrust\Contracts\LaratrustUser;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Profile;
use App\Models\File;
use App\Models\Traits\BaseModelTrait;
use App\Scopes\LanguageScope;
use App\Models\Builders\UserBuilder;
use App\Models\Traits\Relations\Media\HasImageRelationTrait;
use App\Models\Traits\Relations\Media\HasFilesRelationTrait;
use App\Services\ServiceResponse;
use App\Models\Traits\HasMediaTrait;
use App\Models\Traits\ProtectsMainUsers;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
use App\Models\Traits\MainUsersHandling;

/**
 * Attributes
 * @property int id
 * @property string fcm_token
 * @property string username
 * @property string email
 * @property string password
 * @property string phone_no
 * @property int country_id
 * 
 * Relations
 * @property-read User[] user
 * @property-read Profile[] profile
 * 
 * Methods
 * @property-read deleteRelatedItemsUser
 * @property-read checkLastUserWithRole
 * 
 * Configuration & Metadata
 * @property array $appends                   List of accessors to append to model's array form.
 * @property array $eagerLoading              List of relations to eager load dynamically.
 * @property static array $columnsSearch      Fields used for search functionality.
 * @property static array $columnsToExport    Fields exported to Excel or other formats.
 * @property array $casts                     Attribute casting definitions (e.g., enums, dates).
 * @property static array $mainUsers          Stores default/main user names (e.g., from config).
 * 
 **/
class User extends Authenticatable
{
    use BaseModelTrait, HasRoles, HasApiTokens,  HasFactory, Notifiable,SoftDeletes, MainUsersHandling, HasImageRelationTrait, HasFilesRelationTrait ;
    protected $fillable = [
        'id',
        'fcm_token',
        'email',
        'password',
        'phone_no',
        'country_id',
        'is_author',
        'email_verified_at',
        'phone_verified_at',
        'is_active'
    ];
    protected $appends = [];
    
// List of attributes where the *_text accessor should NOT be generated*********************
// protected $preventTextAppends = ['password', 'remember_token'];

    public static array $eagerLoading = ['profile.translations','country', 'roles', 'image', 'files'];
    public static $columnsSearch = ['email', 'phone_no', 'country_id', 'profile.full_name', 'profile.nick_name'];//fields for search
    public static $columnsToExport = ['email', 'phone_no', 'country_id', 'profile.full_name', 'profile.nick_name'];//fields for export

    // List of main user names (e.g., Admin, Super Admin) usually loaded from config at boot time
    public static array $mainUsers = [];
    public static array $mainUsersIds = [];

    // fields for restore && translations
    public static $uniqueFields = ['email', 'phone_no'];

    // Relations to be force deleted with the model
    protected array $forceCascadeDelete = ['profile', 'files', 'image', 'translations'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];
 
    protected $casts = [
         'email_verified_at' => 'datetime',// Automatically converts to datetime
        'password' => 'hashed',// Automatically store password hashed
    ];

    /*** Relations ***/

    /**
     * Get the profile associated with the user.
     *
     * Defines a one-to-one relationship between the User and Profile models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }


   
    /**
     * Get the country that the user belongs to.
     *
     * Defines an inverse one-to-many (belongsTo) relationship between the User and Country models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function savedContents()
    {
        return $this->belongsToMany(Content::class, 'user_saved_contents');
    }

    public function readContents()
    {
        return $this->belongsToMany(Content::class, 'user_read_contents');
    }
    
    /*** Methods ***/
    
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

        $config = [];

        // Custom configuration for report type: 'by_role'
        if ($type === 'by_role') {
            $config = [
                'raw' => 'roles.name as role_name, COUNT(users.id) as users_count',
                'groupBy' => ['roles.name'],
                'join' => [
                    ['model_has_roles', 'model_has_roles.model_id', '=', 'users.id'],
                    ['roles', 'roles.id', '=', 'model_has_roles.role_id'],
                ],
                'joinConditions' => fn($query) => $query->where('model_has_roles.model_type', self::class),
            ];
        } elseif (isset($commonReports[$type])) {
            $config = $commonReports[$type];
        } else {
            $config = [
                'raw' => 'COUNT(id) as ' . modelName($model) . '_count',
                'groupBy' => [],
            ];
        }

        return $config;


        // Default configuration: simple count without any grouping
        return [
            'raw' => 'COUNT(id) as ' . $model_count, // Raw SQL to count total records
            'groupBy' => [], // No grouping applied
        ];
    }

    /**
     * Retrieve a merged list of eager load relationships from both the user and their profile.
     *
     * This is useful when you want to load all relations defined in both the User model
     * and the related Profile model using eager loading in a single query.
     *
     * @return array The combined list of relationship names to eager load.
     */
    public function getEagerLoadingUserProfile()
    {
        $eagerLoadingUser = $this->getProp('eagerLoading'); 
        $eagerLoadingProfile = $this?->profile?->getProp('eagerLoading');

        return array_merge($eagerLoadingUser, $eagerLoadingProfile);
    }

    /**
     * Check if the current user is the last one assigned to a specific role.
     *
     * This can be used to prevent removing the last user from a critical role (e.g., Admin).
     *
     * @param int $roleId The ID of the role to check.
     * @return bool True if this is the last user with the given role, false otherwise.
     */
    public function checkLastUserWithRole($roleId)
    {
        // Count the number of users with the given role
        $userCountWithRole = \DB::table('model_has_roles')
                                ->where('role_id', $roleId)
                                ->where('model_type', $this)
                                ->count();

        // Return true if this is the last user with that role
        return $userCountWithRole <= 1;
    }

    /**
     * @return UserBuilder
     */
    public static function query(): UserBuilder
    {
        return parent::query();
    }

    /**
     * @param $query
     * @return UserBuilder
     */
    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query, $this);
    }
}
