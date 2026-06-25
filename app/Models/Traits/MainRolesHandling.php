<?php
namespace App\Models\Traits;

use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;
use App\Repositories\Base\BaseRepository;

trait MainRolesHandling{
    
        
    /**
     * Get the name of the main role from cache.
     * Falls back to config if cache is not set.
     *
     * @return string|null
     */
    public static function getMainRoleName(): ?string
    {
        return Cache::get('main_role_name', Config::get('spatie_seeder.main_role'));
    }

    /**
     * Check if a given role is the main role.
     *
     * @param string $role
     * @return bool
     */
    public static function isMainRole(string $role): bool
    {
        return $role === self::getMainRoleName();
    }
    
    
     /**
     * Retrieve protected role Names based on main roles
     *
     * @return array
     */
    public static function getMainRolesNames(): array
    {
        // If already loaded in memory, return directly
    if (!empty(static::$mainRolesNames)) {
        return static::$mainRolesNames;
    }
        return static::$mainRolesNames =cache()->rememberForever('main_roles_names', function () {
            return array_keys(config('spatie_seeder.roles_structure', []));
        });
    }

    /**
     * Retrieve protected role IDs based on main roles
     *
     * @return array
     */
    public static function getMainRolesIds(): array
    {
        // If already loaded in memory, return directly
        if (!empty(static::$mainRolesIds)) {
            return static::$mainRolesIds;
        }

        // Otherwise, try cache
        return static::$mainRolesIds = cache()->rememberForever('main_roles_ids', function () {
            $mainRolesNames = static::getMainRolesNames();
            $ids = [];

            foreach ($mainRolesNames as $role) {
                $firstRole = static::where('name', $role)->orderBy('id')->first();
                if ($firstRole) {
                    $ids[] = $firstRole->id;
                }
            }

            return $ids;
        });
    }


     /**
     * Scope: exclude main roles
     */
    public function scopeExceptMain($query)
    {
        return $query->whereNotIn('id', static::getMainRolesIds());
    }

    /**
     * Scope: only trashed, exclude main roles
     */
    public function scopeOnlyTrashedExceptMain($query)
    {
        return $query->onlyTrashed()->whereNotIn('id', static::getMainRolesIds());
    }

     /**
     * Scope: exclude main Roles , without trash
     */
    public function scopeExceptMainWithoutTrashed($query)
    {
        return $query->whereNotIn('id', static::getMainRolesIds())->withoutTrashed();
    }
    /**
     * Find a role by ID, ignoring protected main roles
     *
     * @param int $id
     * @return \App\Models\Role
     * @throws ApiResponseException
     */
    public function findRoleExceptMain($id)
    {
        $auth = getAuthUser();
        if($auth->hasRole('superadmin') || $auth->hasRole('admin')){
            return app(BaseRepository::class)->findOrFailApi($id, \App\Models\Role::class, $forUser = false);
        }
        $role = self::exceptMain()->find($id);
        return $role ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }

    /**
     * Find a trashed role by ID, ignoring protected main roles
     *
     * @param int $id
     * @return \App\Models\Role
     * @throws ApiResponseException
     */
    public function findRoleExceptMainTrash($id)
    {
        $auth = getAuthUser();
        if($auth->hasRole('superadmin') || $auth->hasRole('admin') || $auth->id === (int) $id){
            return app(BaseRepository::class)->findOnlyTrashedOrFail($id, \App\Models\Role::class, $forUser = false);
        }
        $role = self::onlyTrashedExceptMain()->find($id);
        return $role ?? throw new ApiResponseException(ServiceResponseEnum::FORBIDDEN, ServiceResponseEnum::NOT_FOUND);
    }

    /**
     * Find a Role by ID, ignoring protected main Roles , without trash
     *
     * @param int $id
     * @throws \App\Exceptions\ApiResponseException
     */
    public function findRoleExceptMainWithoutTrash($id)
    {
        $auth = getAuthUser();

        if($auth->hasRole('superadmin') || $auth->hasRole('admin')){
            return app(BaseRepository::class)->findOrFailApi($id, \App\Models\Role::class, $forUser = false);
        }

        $role = self::exceptMainWithoutTrashed()->find($id);

        return $role ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }


}