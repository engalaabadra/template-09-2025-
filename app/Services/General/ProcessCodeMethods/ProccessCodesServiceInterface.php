<?php

namespace App\Services\General\ProcessCodeMethods;


interface ProccessCodesServiceInterface{
    // Generic method to process phone or email
    public function processCode($model, $request, $code, $type, $msg = null);
    // Check the code, validate expiration, create or retrieve user, and generate a token
    public function checkCode($model, $code);
    // find a user by code, phone, or email
    public function findCodeUser($model, $code, $infoUser);
}
