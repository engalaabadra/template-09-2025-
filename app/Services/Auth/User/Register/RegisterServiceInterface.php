<?php
namespace App\Services\Auth\User\Register;

interface RegisterServiceInterface{
    public function register($request,$model);
}
