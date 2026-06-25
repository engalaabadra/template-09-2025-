<?php

namespace App\Services\User\Payment\PaymentGateways\Thawani;

use App\Clients\ThawaniHttpClient; // You should ensure this exists
use SandboxEnvironment;            // You should ensure this exists

/**
 * ThawaniService
 *
 * This service handles the creation of the HTTP client and preparation
 * of payment data required to communicate with Thawani's payment gateway.
 * 
 * @package App\Services\User\Payment\PaymentGateways\Thawani
 * 
 */
class ThawaniService
{
    /**
     * The HTTP client instance for communicating with Thawani.
     *
     * @var \ThawaniHttpClient|null
     */
    protected $client;

    /**
     * Create and return the Thawani client instance if it hasn't been created yet.
     *
     * @return \ThawaniHttpClient
     */
    protected function client()
    {
        // Check if the client has not been instantiated yet
        if (!$this->client) {

            // Instantiate the client using the credentials from the payment method's options
            $this->client = new ThawaniHttpClient(
                new SandboxEnvironment(
                    $this->paymentMethod->options['client_id'],     // Retrieve client ID
                    $this->paymentMethod->options['client_secert']   // Retrieve client secret
                )
            );
        }

        // Return the Thawani client
        return $this->client;
    }

    /**
     * Prepare the data required to initiate a payment request to Thawani.
     *
     * @param mixed $order The order data (currently unused)
     * @return array The structured payment data
     */
    protected function setDataPayment($order)
    {
        // Payment data structure
        $data = [
            'client_reference_id' => 'Test payment1', // Unique reference ID for client-side tracking
            'mode' => 'payment',                      // Payment mode
            'products' => [
                [
                    'name' => 'pro1',                 // Product name
                    'amount' => 100,                  // Amount in smallest currency unit
                    'quantity' => 2                   // Quantity of the product
                ]
            ],
            'success_url' => route('payment.return', 'thawani'), // Redirect on success
            'cancel_url' => route('payment.cancel', 'thawani'), // Redirect on cancel
        ];

        // Return the prepared payment data
        return $data;
    }
}
