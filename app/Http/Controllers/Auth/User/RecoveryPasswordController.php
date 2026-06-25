<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\User\ResetPasswordRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Http\Requests\Auth\User\CheckCodeRequest;
use App\Http\Requests\Auth\User\ForgotPasswordRequest;
use App\Services\General\ProcessCodeMethods\ProccessCodesService;
use App\Repositories\Auth\User\Recovery\PasswordRepository;
use App\Services\Auth\User\Recovery\PasswordService as PasswordRecoveryService;
use App\Resources\UserResource;

/**
 * Class RecoveryPasswordController
 *
 * Handles all recovery password operations including:
 * - Forgot password
 * - Code verification
 * - Resend code
 * - Reset password
 */
class RecoveryPasswordController extends BaseController
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var PasswordReset
     */
    protected $passwordReset;

    /**
     * @var PasswordRecoveryService
     */
    protected $passwordRecoveryService;

    /**
     * @var ProccessCodesService
     */
    protected $proccessCodesService;

    /**
     * Inject dependencies via constructor.
     *
     * @param User $user
     * @param PasswordReset $passwordReset
     * @param PasswordRepository $passwordRepo
     * @param PasswordRecoveryService $passwordRecoveryService
     * @param ProccessCodesService $proccessCodesService
     */
    public function __construct(
        User $user,
        PasswordReset $passwordReset,
        PasswordRepository $passwordRepo,
        PasswordRecoveryService $passwordRecoveryService,
        ProccessCodesService $proccessCodesService
    ) {
        $this->user = $user;
        $this->passwordReset = $passwordReset;
        $this->passwordRecoveryService = $passwordRecoveryService;
        $this->proccessCodesService = $proccessCodesService;
    }

    /**
     * Handle forgot password request.
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // Initiate the forgot password process
        $result = $this->passwordRecoveryService->forgotPassword($request, $this->passwordReset);

        // Return the response
        return $this->respond($result);
    }

    /**
     * Check if the submitted code is valid.
     *
     * @param CheckCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCode(CheckCodeRequest $request)
    {
        // Validate request data
        $data = $request->validated();

        // Verify the code using the ProcessCodesService
        $result = $this->proccessCodesService->checkCode($this->passwordReset, $data['code']);
        // Return the result
        return $this->respond($result, UserResource::class);
    }

    /**
     * Resend the password reset code.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendCode()
    {
        // Resend the verification code
        $result = $this->passwordRecoveryService->resendCode($this->passwordReset);

        // Return the result
        return $this->respond($result);
    }

    /**
     * Handle the password reset request.
     *
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Validate the request and reset the password
        $result = $this->passwordRecoveryService->resetPassword($request);

        // Return the response
        return $this->respond($result);
    }
}
