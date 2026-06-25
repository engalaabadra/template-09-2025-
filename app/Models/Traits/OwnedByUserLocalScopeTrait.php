<?php

namespace App\Models\Traits;

trait OwnedByUserLocalScopeTrait
{
    /**
     * Local scope to filter a query by the authenticated user or a given user.
     *
     * This scope applies a `where` condition on the `$ownerKey` column
     * (default is `user_id`) to limit results to the specified user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param \App\Models\User|int|null $user Optional. A User model or user ID. 
     *                                        If null, uses the currently authenticated user.
     * @param string $ownerKey Optional. The column name to filter by (default: 'user_id')
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @example
     * // Using currently authenticated user
     * $posts = Post::OwnedByUser()->get();
     *
     * // Passing a specific User model
     * $user = User::find(5);
     * $posts = Post::OwnedByUser($user)->get();
     *
     * // Passing a user ID directly
     * $posts = Post::OwnedByUser(5)->get();
     */
    public function scopeOwnedByUser($query, $user = null, $ownerKey = 'user_id')
    {
        // If no user is passed, use the currently authenticated user
        $user ??= $this->getAuthUser();

        // If there's no user, do not modify the query
        if (!$user) {
            return $query;
        }

        // Determine the user ID: accept either a numeric ID or a User model
        $userId = is_numeric($user) ? $user : $user->id;

        // Apply the where clause to filter by owner
        return $query->where($ownerKey, $userId);
    }

}
