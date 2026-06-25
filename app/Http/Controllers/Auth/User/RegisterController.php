<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\User\CheckCodeRequest;
use App\Http\Requests\Auth\User\RegisterRequest;
use App\Models\RegisterCodeNum;
use App\Services\General\ProcessCodeMethods\ProccessCodesService;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Services\Auth\User\Register\RegisterService;

/**
 * Class RegisterController
 *
 * This controller handles the user registration process,
 * including registering new users, verifying the registration code,
 * and resending the verification code if needed.
 */
class RegisterController extends BaseController
{
    /**
     * The User model instance.
     *
     * @var User
     */
    protected $user;

    /**
     * The RegisterCodeNum model instance used for code verification.
     *
     * @var RegisterCodeNum
     */
    protected $registerCodeNum;

    /**
     * The service responsible for handling the registration logic.
     *
     * @var RegisterService
     */
    protected $regService;

    /**
     * The service responsible for processing verification codes.
     *
     * @var ProccessCodesService
     */
    protected $proccessCodesService;

    /**
     * RegisterController constructor.
     *
     * @param User $user
     * @param RegisterService $regService
     * @param RegisterCodeNum $registerCodeNum
     * @param ProccessCodesService $proccessCodesService
     */
    public function __construct(
        User $user,
        RegisterService $regService,
        RegisterCodeNum $registerCodeNum,
        ProccessCodesService $proccessCodesService
    ) {
        $this->user = $user;
        $this->registerCodeNum = $registerCodeNum;
        $this->regService = $regService;
        $this->proccessCodesService = $proccessCodesService;
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Call the registration logic from the service
        $result = $this->regService->register($request, $this->registerCodeNum);

        // Return formatted JSON response
        return $this->respond($result);
    }

    /**
     * Verify the registration code.
     *
     * @param CheckCodeRequest $request
     * @return JsonResponse
     */
    public function checkCodeRegister(CheckCodeRequest $request): JsonResponse
    {
        // Validate the input data
        $data = $request->validated();

        // Verify the code using the service
        $result = $this->proccessCodesService->checkCode($this->registerCodeNum, $data['code']);

        // Return formatted JSON response
        return $this->respond($result);
    }

    /**
     * Resend the registration verification code.
     *
     * @return JsonResponse
     */
    public function resendCodeRegister(): JsonResponse
    {
        // Call the resend logic from the service
        $result = $this->regService->resendCode($this->registerCodeNum);

        // Return formatted JSON response
        return $this->respond($result);
    }
}
