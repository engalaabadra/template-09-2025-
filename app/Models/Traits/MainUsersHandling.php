<?php
namespace App\Models\Traits;

use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;
use App\Repositories\Base\BaseRepository;

trait MainUsersHandling{

    /**
     * Retrieve protected user IDs based on main roles.
     *
     * @return array<int>
     */
    protected static function getMainUsersIds(): array
    {
        // If already loaded in memory, return directly
        if (!empty(static::$mainUsersIds)) {
            return static::$mainUsersIds;
        }

        // Otherwise, try cache
        return static::$mainUsersIds = cache()->rememberForever('main_users_ids', function () {
            $mainRolesNames = \App\Models\Role::getMainRolesNames();
          $ids = [];
  
            foreach ($mainRolesNames as $role) {
                 $role = \App\Models\Role::where('name', $role)
                    ->with(['users' => function ($q) {
                        $q->orderBy('id'); // order by id ascending
                    }])
                    ->first();

                $firstUser = $role?->users->first();
                if ($firstUser) {
                    $ids[] = $firstUser->id;
                }
            }

            return $ids;
        });
    }

   

     /**
     * Scope: exclude main Users
     */
    public function scopeExceptMain($query)
    {
        return $query->whereNotIn('id', static::getMainUsersIds());
    }

     /**
     * Scope: exclude main Users , without trash
     */
    public function scopeExceptMainWithoutTrashed($query)
    {
        return $query->whereNotIn('id', static::getMainUsersIds())->withoutTrashed();
    }

    /**
     * Scope: only trashed, exclude main Users
     */
    public function scopeOnlyTrashedExceptMain($query)
    {
        dd(8);
        return $query->onlyTrashed()->whereNotIn('id', static::getMainUsersIds());
    }

       /**
     * Find a user by ID, ignoring protected main users
     *
     * @param int $id
     * @return \App\Models\User
     * @throws \App\Exceptions\ApiResponseException
     */
    public function findUserExceptMain($id)
    {
        $auth = getAuthUser();

        if($auth->hasRole('superadmin') || $auth->hasRole('admin') || $auth->id === (int) $id){
            return app(BaseRepository::class)->findOrFailApi($id, \App\Models\User::class, $forUser = false);
        }
        $user = self::exceptMain()->find($id);

        return $user ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }


    /**
     * Find a user by ID, ignoring protected main users , without trash
     *
     * @param int $id
     * @return \App\Models\User
     * @throws \App\Exceptions\ApiResponseException
     */
    public function findUserExceptMainWithoutTrash($id)
    {
        $auth = getAuthUser();

        if($auth->hasRole('superadmin') || $auth->hasRole('admin') || $auth->id === (int) $id){
            return app(BaseRepository::class)->findOrFailApi($id, \App\Models\User::class, $forUser = false);
        }

        $user = self::exceptMainWithoutTrashed()->find($id);

        return $user ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }

    /**
     * Find a trashed user by ID, ignoring protected main users
     *
     * @param int $id
     * @return \App\Models\User
     * @throws \App\Exceptions\ApiResponseException
     */
    public function findUserExceptMainTrash($id)
    {
        $auth = getAuthUser();
        if($auth->hasRole('superadmin') || $auth->hasRole('admin') || $auth->id === (int) $id){
            return app(BaseRepository::class)->findOnlyTrashedOrFail($id, \App\Models\User::class, $forUser = false);
        }
        $user = self::onlyTrashedExceptMain()->find($id);

        return $user ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }

}
