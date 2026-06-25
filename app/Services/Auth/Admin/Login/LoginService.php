<?php
namespace App\Services\Auth\Admin\Login;

use Illuminate\Support\Facades\Hash;
use App\Repositories\Auth\Admin\Login\LoginRepository;
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

        // Attempt to find the admin by email or phone and country ID
        $admin = $this->loginRepository->findUserWithRolesByEmailOrPhone(
            $emailOrPhone,
            $request->get('country_id')
        );

        // If admin not found or password is incorrect, return a bad request response
        if (!$admin || !Hash::check($request->get('password'), $admin->password)) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.Invalid credentials'), [
                'password' => ['validation.invalid_password']
            ]);
        }

        $roles = $admin->roles->pluck('name')->toArray();
        
        if(!in_array('superadmin',$roles) && !in_array('admin',$roles)){
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.Invalid credentials'));
        } 
        
        // If FCM token is present, update it for the admin
        if ($request->has('fcm_token')) {
            $admin->update(['fcm_token' => $request->fcm_token]);
        }

        // Prepare the response data including admin and access token
        $token = createToken($admin, 'admin-token');
        $token->token->guard_name = 'admin';
        $token->token->save();

        $data = [
            'admin'  => $admin,
            'token' => $token->accessToken,
        ];

        return $data;
    }
}
