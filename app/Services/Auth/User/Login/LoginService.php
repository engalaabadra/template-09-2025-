<?php
namespace App\Services\Auth\User\Login;

use Illuminate\Support\Facades\Hash;
use App\Repositories\Auth\User\Login\LoginRepository;
use App\Services\ServiceResponse;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;

/**
 * Class LoginService
 *
 * Handles user login functionality.
 */
class LoginService implements LoginServiceInterface
{
    /**
     * @var LoginRepository Instance of the login repository to interact with user data.
     */
    protected $loginRepository;

    /**
     * LoginService constructor.
     *
     * @param LoginRepository $loginRepository The login repository dependency.
     */
    public function __construct(LoginRepository $loginRepository)
    {
        // Assign the repository to the class property
        $this->loginRepository = $loginRepository;
    }

    /**
     * Handle user login request.
     *
     * @param LoginRequest $request The validated login request data.
     * @return object Response containing user and access token or error.
     */
    public function login($request)
    {
        // Retrieve email or phone number from the request
        $emailOrPhone = $request->get('email') ?: $request->get('phone_no');

        // Attempt to find the user by email or phone and country ID
        $user = $this->loginRepository->findUserWithRolesByEmailOrPhone(
            $emailOrPhone,
            $request->get('country_id')
        );

        // If user not found or password is incorrect, return a bad request response
        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.Invalid credentials'), [
                'password' => ['validation.invalid_password']
            ]);
            
        }

        // If FCM token is present, update it for the user
        if ($request->has('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Prepare the response data including user and access token
        $token = createToken($user, 'user-token');
        $token->token->guard_name = 'user';
        $token->token->save();


        // Prepare the response data including user and access token
        $data = [
            'user'  => $user,
            'token' => $token->accessToken,
        ];

        // Return the successful login response
        return $data;
    }
}
