<?php

namespace App\Services\General\ProcessCodeMethods;

use App\Models\PasswordReset;
use App\Models\RegisterCodeNum;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\General\SendingMessageMethods\SendingMessagesService;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceResponse;
use App\Exceptions\ApiResponseException;

class ProccessCodesService implements ProccessCodesServiceInterface{
    /**
     * Generic method to process phone or email verification codes
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $request
     * @param  string  $code
     * @param  string  $type  'phone_no' or 'email'
     * @param  string|null  $msg
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function processCode($model, $request, $code, $type, $msg = null)
    {
        $data = [];

        // Prepare the data depending on whether type is phone or email
        if ($type === 'phone_no') {
            $data = [
                'phone_no'    => $request['phone_no'],
                'country_id'  => $request['country_id'],
                'code'        => $code,
            ];
        } elseif ($type === 'email') {
            $data = [
                'code'   => $code,
                'email'  => $request['email'],
                'type'   => 'check-code',
            ];
        }

        // Optional: Send SMS or Email
        // if ($msg) app(SendingMessagesService::class)->sendingMessage($data);

        // Create or update the code record based on phone/email
        return $model->updateOrCreate(
            [$type => $request[$type]],
            $data
        );
    }

    /**
     * Check verification code, validate expiration, and return user and token
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $code
     * @return array|\App\Services\ServiceResponse
     */
    public function checkCode($model, $code)
    {
        // Retrieve user info from cache (saved in registration step)
        $infoUser = Cache::get('info_user');

        // If no user info found, return error
        if (!$infoUser) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.No registration information found, because you must go into register step before this'));
        }
        
        // Search for matching code in the model
        $resultCodeUser = $this->findCodeUser($model, $code, $infoUser);

        // If no matching code found
        if (!$resultCodeUser) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.code is invalid, try again'));
        }

        // If the code is older than 1 hour, delete and return error
        if ($resultCodeUser->created_at->lt(now()->subHour())) {
            $resultCodeUser->delete();
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.code has expired'));
        }

        // If using RegisterCodeNum model (during registration)
        if ($model instanceof RegisterCodeNum) {

            // If email exists, update/create email code
            if (isset($infoUser['email'])) {
                $model->updateOrCreate(
                    ['email' => $infoUser['email']],
                    ['code'  => $code]
                );
            }

            // If phone number exists, update/create phone code
            if (isset($infoUser['phone_no'])) {
                $model->updateOrCreate(
                    [
                        'phone_no'   => $infoUser['phone_no'],
                        'country_id' => $infoUser['country_id'],
                    ],
                    ['code' => $code]
                );
            }

            // Create or get the user
            $user = User::firstOrCreate(
                [
                    'email'      => $infoUser['email']      ?? null,
                    'phone_no'   => $infoUser['phone_no']   ?? null,
                    'country_id' => $infoUser['country_id'] ?? null,
                ],
                [
                    'password' => $infoUser['password'],
                    'username' => 'user',
                ]
            );

            // Find 'user' role id
            $roleUserId = Role::where('name', 'user')->first()->id;

            // Attach role if not already assigned
            if (!$user->roles()->where('id', $roleUserId)->exists()) {
                $user->roles()->attach($roleUserId);
            }

            // Return user with token
            return [
                'user'  => $user,
                'token' => $user->createToken('token')->accessToken,
                'code'  => $code,
            ];
        }

        // For login or other verification (non-register), just return the user
        $user = isset($infoUser['email'])
            ? User::where('email', $infoUser['email'])->first()
            : User::where([
                'phone_no'   => $infoUser['phone_no'],
                'country_id' => $infoUser['country_id'],
            ])->first();
        return [
            'user' => $user,
            'code' => $code,
        ];
    }

    /**
     * Search user by verification code using phone number or email
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $code
     * @param  array  $infoUser
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findCodeUser($model, $code, $infoUser)
    {
        // If phone number is available, try finding code by phone
        if (!empty($infoUser['phone_no'])) {
            $user = $model->where('code', $code)
                        ->where('phone_no', $infoUser['phone_no'])
                        ->where('country_id', $infoUser['country_id'] ?? null)
                        ->first();

            if ($user) {
                return $user;
            }
        }

        // If email is available, try finding code by email
        if (!empty($infoUser['email'])) {
            return $model->where('code', $code)
                        ->where('email', $infoUser['email'])
                        ->first();
        }

        // No matching record found
        return null;
    }


}
