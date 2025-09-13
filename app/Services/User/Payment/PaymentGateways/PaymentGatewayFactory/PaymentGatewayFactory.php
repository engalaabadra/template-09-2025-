<?php

namespace App\Services\User\Payment\PaymentGateways\PaymentGatewayFactory;

use App\Entities\Payment;
use Illuminate\Support\Str;
use App\Services\User\PaymentGateways\Paypal\Paypal;

/**
 * Class PaymentGatewayFactory
 *
 * This factory class is responsible for dynamically instantiating
 * the correct payment gateway class based on the given gateway name.
 * 
 * @package App\Services\User\Payment\PaymentGateways\PaymentGatewaysFactory
 * 
 */
class PaymentGatewayFactory
{
    /**
     * Create and return an instance of a payment gateway class based on the given name.
     *
     * @param string $name The name of the payment gateway (e.g., 'paypal', 'stripe').
     * @return \App\Services\User\PaymentGateways\PaymentGateway
     *
     * @throws \Exception If the gateway class does not exist or cannot be instantiated.
     */
    public static function create($name)
    {
        // Build the fully qualified class name using StudlyCase for the namespace and class
        $class = 'App\Services\User\PaymentGateways\\' . Str::studly($name) . '\\' . Str::studly($name);

        try {
            // Try to instantiate and return the gateway class
            return new $class();
        } catch (\Exception $e) {
            // Throw a specific exception if the class cannot be found
            throw new \Exception("Payment gateway {{$name}} not found ");
        }

        // Redundant fallback (will never reach here if exception is thrown)
        return new $class();
    }
}
