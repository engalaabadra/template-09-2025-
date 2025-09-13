<?php
namespace App\Services\Auth\User\Register;

use App\Services\General\ProcessCodeMethods\ProccessCodesService;
use App\Models\User;
use App\Models\RegisterCodeNum;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceResponse;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;

/**
 * Service class responsible for handling user registration,
 * including sending initial registration codes and resending them via phone or email.
 */
class RegisterService implements RegisterServiceInterface
{
    /**
     * @var ProccessCodesService
     */
    protected $proccessCodesService;

    /**
     * RegisterService constructor.
     *
     * @param ProccessCodesService $proccessCodesService
     */
    public function __construct(ProccessCodesService $proccessCodesService)
    {
        $this->proccessCodesService = $proccessCodesService;
    }

    /**
     * Handle user registration logic.
     *
     * @param RegisterRequest $request
     * @param mixed $model
     * @return object
     */
    public function register($request, $model)
    {
        // Validate the request and get the input data
        $data = $request->validated();

        // Generate a unique registration code
        $data['code'] = getCode();

        // Handle registration by phone number if provided
        if ($request->filled('phone_no')) {
            $resultReg = $this->proccessCodesService->processCode(
                $model,
                $request,
                $data['code'],
                $type = 'phone_no',
                trans('messages.pls, use this code :') . $data['code']
            );
        }

        // Handle registration by email if provided
        if ($request->filled('email')) {
            $resultReg = $this->proccessCodesService->processCode(
                $model,
                $request,
                $data['code'],
                $type = 'email'
            );
        }

        // If result is an error message (string), return it
        if (is_string($resultReg)) {
            return $resultReg;
        }

        // Store registration info in cache
        Cache::put('info_user', $data);

        // Return the data to be used in the response
        return $data;
    }

    /**
     * Resend registration code to phone or email based on cached user data.
     *
     * @param RegisterCodeNum $model
     * @return object
     */
    public function resendCode($model)
    {
        // Retrieve user info from cache
        $infoUser = Cache::get('info_user');

        // Return error if registration info is missing from cache
        if (
            !$infoUser ||
            (!isset($infoUser['phone_no']) && !isset($infoUser['email']))
        ) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.No registration information found, so You must make register before this'));
        }

        // Generate a new code
        $code = getCode();
        $infoUser['code'] = $code;

        // Resend code via phone if phone number exists
        if (isset($infoUser['phone_no'])) {
            $msg = 'code change password:' . $code . 'use this immediatly';
            return $this->proccessCodesService->processCode(
                $model,
                $infoUser,
                $code,
                $type = 'phone_no',
                $msg
            );
        }

        // Resend code via email if email exists
        if (isset($infoUser['email'])) {
            return $this->proccessCodesService->processCode(
                $model,
                $infoUser,
                $code,
                $type = 'email'
            );
        }

        // Fallback response with email, phone, and code
        return [
            'email'     => $infoUser['email'] ?? null,
            'phone_no'  => $infoUser['phone_no'] ?? null,
            'code'      => $infoUser['code'],
        ];
    }
}
