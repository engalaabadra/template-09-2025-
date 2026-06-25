<?php

namespace App\Http\Controllers\User;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\Profile;
use App\Http\Requests\User\Profile\UpdateProfileRequest;
use App\Http\Requests\User\Profile\UpdatePasswordRequest;
use App\Resources\ProfileResource;
use App\Services\User\Profile\ProfileService;
use App\Repositories\User\Profile\ProfileRepository;
use App\Http\Requests\Image\UploadImageRequest;
use App\Http\Requests\File\UploadFilesRequest;
use App\Http\Requests\File\DeleteFilesRequest;

/**
 * Class ProfileController
 *
 * This controller handles profile-related operations for users,
 * including showing profile data, updating profile information,
 * and changing user passwords.
 */
class ProfileController extends BaseController
{
    #region Constructor

    /**
     * @var ProfileService Handles business logic
     */
    protected $profileService;

    /**
     * @var ProfileRepository Handles data access layer
     */
    protected $profileRepo;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Profile
     */
    protected $profile;

    /**
     * ProfileController constructor.
     *
     * @param ProfileService     $profileService  The service handling profile logic.
     * @param ProfileRepository  $profileRepo     The repository for profile data.
     * @param User               $user            The user model.
     * @param Profile            $profile         The profile model.
     */
    public function __construct(ProfileService $profileService, ProfileRepository $profileRepo, User $user, Profile $profile)
    {
        $this->profileService = $profileService;
        $this->profileRepo    = $profileRepo;
        $this->user           = $user;
        $this->profile        = $profile;
    }
    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: show($id) ====================

    /**
     * Show the authenticated user's profile.
     *
     * @return Responsable
     */
    public function show()
    {
        // Get the user's profile using the repository
        $result = $this->profileRepo->show($this->user);

        // Return the formatted response using the ProfileResource
        return $this->respond($result, ProfileResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    
    #region ===================== Start CRUD Methods:  update(Request $request, $id) =====================

    /**
     * Update the user's profile.
     *
     * @param UpdateProfileRequest $request  Validated request data for profile update.
     * @return Responsable
     */
    public function update(UpdateProfileRequest $request)
    {
        // Update profile using the service
        $result = $this->profileService->update($request, $this->profile);
        // Return the updated profile as a resource
        return $this->respond($result, ProfileResource::class);
    }
    #endregion ===================== End CRUD Methods(Storing) =====================

    /**
     * Update the user's password.
     *
     * @param UpdatePasswordRequest $request  Validated request data for password update.
     * @return Responsable
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        // Update password using the service
        $result = $this->profileService->updatePassword($request, $this->user);

        // Return the response (can be success message or user profile)
        return $this->respond($result, ProfileResource::class);
    }

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a file for a user.
     *
     * @param UploadImageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFile(UploadImageRequest $request)
    {
        $result = $this->profileService->uploadFile($request, $this->user);

        return $this->respond($result, ProfileResource::class);
    }

    /**
     * Delete a file from a user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile()
    {
        $result = $this->profileService->deleteFile($this->user);

        return $this->respond($result);
    }
    /**
     * Upload files to a specific user.
     *
     * @param UploadFilesRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFiles(UploadFilesRequest $request)
    {
        $this->profile->setProp('eagerLoading', ['files']);
        $result = $this->profileService->uploadFiles($request, $this->user);
        return $this->respond($result, ProfileResource::class);
    }

    /**
     * Delete files from a specific user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFiles(DeleteFilesRequest $request)
    {
        $result = $this->profileService->deleteFiles($request, $this->user);
        return $this->respond($result);
    }
    #endregion ===================== End File Handling Methods =====================

}
