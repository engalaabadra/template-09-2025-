<?php
namespace App\Repositories\Auth\User\Login;

interface LoginRepositoryInterface{
    public function findUserWithRolesByEmailOrPhone($emailOrPhone, $countryId);
}
