<?php

namespace App\Services\User\Payment\PaymentGateways\PaymentGatewayFactory;

use App\Models\Payment;

interface PaymentGatewayFactoryInterface{
    public function create($order,$user);//return url
    public function verify($id) : Payment;
    public function formOptions() : array;
}
