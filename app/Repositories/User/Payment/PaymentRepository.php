<?php
namespace App\Repositories\User\Payment;

use App\Repositories\PaymentMethodRepositoryInterface;
 
use Illuminate\Support\Facades\Session;
use  App\Models\PaymentMethod;

/**
 * PaymentRepository
 *
 * This is a Payment Repository class implementing the PaymentRepositoryInterface.
 */
class PaymentRepository implements PaymentMethodRepositoryInterface
{

    public function callback(){
        //will get gateway from payment id in session that put it in create meth 
        $gateway = PaymentMethod::where('id',Session::get('payment_method_id'))->first();
        return $gateway->verify($gateway);//return object payment
    }

    

}