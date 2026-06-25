<?php
namespace App\Services\Auth\User\Recovery;

use App\Services\General\MsegatSmsService;
use App\Models\User;
use App\Services\General\SendingMessageMethods\SendingMessagesService;
use App\Repositories\Auth\User\Recovery\passwordRepository;
use App\Services\General\ProcessCodeMethods\ProccessCodesService;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceResponse;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;

/**
 * Service class responsible for handling password reset functionality,
 * including forgot password, resending codes, and resetting passwords.
 *
 * Implements the PasswordServiceInterface to ensure contract consistency.
 */
class PasswordService implements PasswordServiceInterface
{
    /**
     * Repository to handle password-related database operations.
     *
     * @var PasswordRepository
     */
    protected $passwordRepository;

    /**
     * Service responsible for handling code generation and delivery.
     *
     * @var ProccessCodesService
     */
    protected $proccessCodesService;

    /**
     * PasswordService constructor.
     *
     * @param PasswordRepository     $passwordRepository
     * @param ProccessCodesService   $proccessCodesService
     */
    public function __construct(
        PasswordRepository $passwordRepository,
        ProccessCodesService $proccessCodesService
    ) {
        $this->passwordRepository     = $passwordRepository;
        $this->proccessCodesService   = $proccessCodesService;
    }

    /**
     * Handle forgot password logic by generating and sending reset code.
     *
     * @param ForgotPasswordRequest $request
     * @param User                  $model    The user model or password reset model
     *
     * @return object
     */
    public function forgotPassword($request, $model)
    {
        // Validate request data
        $data = $request->validated();

        // Generate a random code for password reset
        $data['code'] = getCode();

        // Handle phone-based reset
        if ($request->has('phone_no')) {
            $msg = "رمز تغيير كلمة المرور:" . $data['code'] . " يرجى استخدامه فورًا.";

            $result = $this->proccessCodesService->processCode(
                $model,
                $request,
                $data['code'],
                $type = 'phone_no',
                $msg
            );

            if (is_string($result)) {
                throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, $result);
            }
        }

        // Handle email-based reset
        if ($request->has('email')) {
            $result = $this->proccessCodesService->processCode(
                $model,
                $request,
                $data['code'],
                $type = 'email'
            );

            if (is_string($result)) {
                throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, $result);
            }
        }

        // Store user info in cache
        Cache::put('info_user', $data);

        return $data;
    }

    /**
     * Resend the password reset code to the user (email or phone).
     *
     * @param PasswordReset $model
     *
     * @return object
     */
    public function resendCode($model)
    {
        // Retrieve stored user info from cache
        $infoUser = Cache::get('info_user');

        // Validate if the user data is present and complete
        if (
            !$infoUser ||
            (!isset($infoUser['phone_no']) && !isset($infoUser['email']))
        ) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.No registration information found, so You must make forgot password before this'));
        }

        // Generate new reset code
        $code = getCode();
        $infoUser['code'] = $code;

        // Resend code via phone if available
        if (isset($infoUser['phone_no'])) {
            $msg = 'code change password:' . $code . 'use this immediatly';
            $this->proccessCodesService->processCode(
                $model,
                $infoUser,
                $code,
                $type = 'phone_no',
                $msg
            );
        }

        // Resend code via email if available
        if (isset($infoUser['email'])) {
            $this->proccessCodesService->processCode(
                $model,
                $infoUser,
                $code,
                $type = 'email'
            );
        }

        // Return relevant contact info and the new code
        return [
            'email'     => isset($infoUser['email']) ? $infoUser['email'] : null,
            'phone_no'  => isset($infoUser['phone_no']) ? $infoUser['phone_no'] : null,
            'code'      => $infoUser['code'],
        ];
    }

    /**
     * Reset the user's password using the validated code.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return object
     */
    public function resetPassword($request)
    {
        // Get validated request data
        $data = $request->validated();

        // Retrieve user info from cache
        $infoUser = Cache::get('info_user');

        // Check if user data exists
        if (
            !$infoUser ||
            (!isset($infoUser['phone_no']) && !isset($infoUser['email']))
        ) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.No registration information found, so You must make forgot password before this'));
        }

        // Find the user using email or phone
        $resultUser = $this->passwordRepository->findUserByEmailOrPhone($infoUser);

        // Update the user's password
        $resultUser->update([
            'password' => $data['password'],
        ]);

        return $resultUser;
    }
}