<?php
namespace App\Repositories\Auth\Admin\Login;

interface LoginRepositoryInterface{
    public function findUserWithRolesByEmailOrPhone($emailOrPhone, $countryId);
}
