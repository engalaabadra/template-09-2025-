<?php

namespace App\Services\User\Payment;

use Illuminate\Support\Facades\Session;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\User\PaymentGateways\PaymentGatewayFactory\PaymentGatewayFactory;
use App\Traits\PaymentMethodTapTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class WalletService
 *
 * This service handles the payment process using the Tap wallet payment method.
 * It implements the PaymentGatewayFactory interface for consistency across payment gateways.
 * 
 * @package App\Services\User\Payment
 * 
 */
class WalletService implements PaymentGatewayFactory
{
    use PaymentMethodTapTrait;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var string
     */
    protected string $tapBaseUrl = 'https://api.tap.company/v2';

    /**
     * @var string
     */
    protected string $tapAuthSecret;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * WalletService constructor.
     *
     * @param PaymentMethod $paymentMethod
     * @param PaymentService $paymentService
     * @param mixed $options
     */
    public function __construct(PaymentMethod $paymentMethod, PaymentService $paymentService, $options)
    {
        $this->paymentMethod = $paymentMethod;
        $this->paymentService = $paymentService;
        $this->tapAuthSecret = config('services.tap.secret_test');
    }

    /**
     * Create a payment session and return the payment URL.
     *
     * @param mixed $order
     * @param mixed $user
     * @return string
     */
    public function create($order, $user): string
    {
        // Prepare redirect URL for the payment callback
        $data['redirect']['url'] = url(route("api.payments.callback", [$order->id]));

        // Set the payment data payload
        $data = $this->setDataPayment($order, $user);

        // Send payment creation request via cURL
        $response = $this->setCurl();

        // Check for errors in the response
        if ($response && isset($response->errors)) {
            return $response;
        }

        // Store transaction/session ID and payment ID in session
        Session::put('transaction_id', $response['session_id']);
        Session::put('payment_id', $payment->id);

        // Return the redirect URL to the payment page
        return $response->transaction->url;
    }

    /**
     * Verify the payment after the callback and update the payment status.
     *
     * @return Payment
     */
    public function verify(): Payment
    {
        // Retrieve payment details from session
        $resultPayment = $this->paymentService->getPayment();

        try {
            // Check payment status with Tap after callback
            $response = $this->checkDataPaymentCallback();

            // If there are errors in the response, return them
            if (isset($response->errors)) {
                return response()->json($response->errors);
            }

            // Handle successful payment
            if ($response->status === 'CAPTURED') {
                // Update payment status to 1 (success)
                $payment = $this->updatePayment($resultPayment, $response, 1);
            } else {
                // Update payment status to -1 (failed)
                $payment = $this->updatePayment($resultPayment, $response, -1);
            }

            // Clear session data
            Session::forget(['payment_id', 'session_id']);

            return $payment;
        } catch (HttpException $ex) {
            // Handle exception during callback verification
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }
}
