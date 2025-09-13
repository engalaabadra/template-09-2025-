<?php
namespace App\Services\Auth\User\Recovery;

interface PasswordServiceInterface{
    public function forgotPassword($request,$model);
    public function resendCode($model);
    public function resetPassword($request);
}
