<?php

namespace App\Services\User\Payment\PaymentGateways\Paypal;

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use App\Models\PaymentMethod;

/**
 * Class PaypalService
 *
 * This service handles the setup and data preparation for PayPal payment gateway.
 * It creates the PayPal client instance and sets the payment data for orders.
 * 
 * @package App\Services\User\Payment\PaymentGateways\Paypal
 * 
 */
class PaypalService
{
    /**
     * @var PayPalHttpClient|null
     */
    public $client;

    /**
     * @var PaymentMethod|null
     */
    protected $paymentMethod;

    /**
     * PaypalService constructor.
     *
     * Retrieves PayPal payment method configuration from the database.
     */
    public function __construct()
    {
        // Fetch the PayPal payment method from the database using slug
        $this->paymentMethod = PaymentMethod::where('slug', 'paypal')->first();
    }

    /**
     * Initializes and returns the PayPal client using credentials stored in the payment method.
     *
     * @return PayPalHttpClient
     * @throws \Exception If client ID or secret is missing
     */
    public function client(): PayPalHttpClient
    {
        // If the client hasn't been created yet, create and return it
        if (!$this->client) {

            // Validate that both client ID and secret exist
            if (
                empty($this->paymentMethod->options['client_id']) ||
                empty($this->paymentMethod->options['client_secret'])
            ) {
                \Log::error('PayPal Client ID or Secret is missing.');
                throw new \Exception('Invalid PayPal credentials.');
            }

            // Create a new PayPal client instance using sandbox environment
            $this->client = new PayPalHttpClient(
                new SandboxEnvironment(
                    $this->paymentMethod->options['client_id'] ?? null,
                    $this->paymentMethod->options['client_secret'] ?? null
                )
            );
        }

        return $this->client;
    }

    /**
     * Prepares and returns the data structure required to create a PayPal order.
     *
     * @param mixed $order The order to be paid
     * @return OrdersCreateRequest The request object with order data
     */
    public function setDataPayment($order): OrdersCreateRequest
    {
        // Create a new order request object
        $request = new OrdersCreateRequest();

        // Set the response format preference
        $request->prefer('return=representation');

        // Set the body of the PayPal order request
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "test_ref_id1", // Can be changed to order ID
                "amount" => [
                    "value" => 10,
                    "currency_code" => "USD"
                ]
            ]],
            "application_context" => [
                "cancel_url" => route('payment.cancel', 'paypal'), // Redirect if user cancels
                "return_url" => route('payment.return', 'paypal'), // Redirect after successful payment
            ]
        ];

        return $request;
    }
}
