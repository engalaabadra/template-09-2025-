<?php

namespace App\Observers;

use App\Models\File;
use App\Observers\UserObserver;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
use App\Traits\Observers\ProtectedActionsTrait;

/**
 * Class FileObserver
 *
 * Handles model events for the File model.
 * Specifically, it prevents creating or deleting files
 * that belong to protected users (e.g., Super Admin).
 */
class FileObserver
{
    // Use common helper methods for throwing exceptions
    use ProtectedActionsTrait;

    /**
     * Handle the "creating" event.
     * Prevents uploading a file if the owner (fileable) is a protected user.
     *
     * @param File $file
     * @throws ApiResponseException
     */
    public function creating(File $file)
    {
        // Check if the file is attached to a User model
        if ($file->fileable_type === \App\Models\User::class) {
            // Get the related user
            $user = $file->fileable;

            // If the user is protected → block file upload
            if (UserObserver::isProtectedUser($user)) {
                throw new ApiResponseException(
                    ServiceResponseEnum::FORBIDDEN,
                    "Cannot upload file for protected user: {$user->email}"
                );
            }
        }
    }

    /**
     * Handle the "deleting" event.
     * Prevents deleting a file if the owner (fileable) is a protected user.
     *
     * @param File $file
     * @throws ApiResponseException
     */
    public function deleting(File $file)
    {
        // Check if the file is attached to a User model
        if ($file->fileable_type === \App\Models\User::class) {
            // Get the related user
            $user = $file->fileable;

            // If the user is protected → block file deletion
            if (UserObserver::isProtectedUser($user)) {
                throw new ApiResponseException(
                    ServiceResponseEnum::FORBIDDEN,
                    "Cannot delete file of protected user: {$user->email}"
                );
            }
        }
    }
}
