<?php
namespace App\Repositories\Auth\User\Recovery;

interface PasswordRepositoryInterface{
    public function findUserByEmailOrPhone($data);
}
