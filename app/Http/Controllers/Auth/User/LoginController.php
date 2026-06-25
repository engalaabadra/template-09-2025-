<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\User\LoginRequest;
use Illuminate\Http\Request;
use App\Services\Auth\User\Login\LoginService;
use App\Resources\ProfileResource;
use App\Enums\ServiceResponseEnum;
use App\Services\ServiceResponse;
use App\Exceptions\ApiResponseException;

/**
 * Class LoginController
 *
 * Handles user authentication and logout actions.
 */
class LoginController extends BaseController
{
    /**
     * @var LoginService
     */
    protected $loginService;

    /**
     * Inject the LoginService.
     *
     * @param LoginService $loginService
     */
    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        // Attempt to login the user through the LoginService
        $result = $this->loginService->login($request);

        // Return the login result as a JSON response
        return $this->respond($result);
    }

    /**
     * Destroy an authenticated session (logout).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        // Check if the authenticated user has the "user" role
        if (!userApi()?->hasRole('user')) {
            throw new ApiResponseException(ServiceResponseEnum::FORBIDDEN);
        }

        // Revoke the current user's access token
        userApi()->token()->revoke();

        // Return a success response
        return $this->respond();
    }
}
