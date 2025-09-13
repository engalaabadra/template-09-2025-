<?php

namespace App\Services\User\Payment\PaymentGateways\Tap;

use Illuminate\Support\Facades\Session;
use App\Methods\Payment;
use App\Methods\PaymentMethod;
use App\Services\User\Payment\PaymentGateways\PaymentGatewayInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Tap
 *
 * This class handles Tap payment gateway integration including
 * creating payment requests and verifying completed payments.
 * 
 * @package App\Services\User\Payment\PaymentGateways\Tap
 * 
 */
class Tap implements PaymentGatewayInterface
{
    /**
     * The payment method instance.
     *
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * The Tap service handler instance for API communication.
     *
     * @var TapService
     */
    protected $tapService;

    /**
     * The main payment service instance used for common payment operations.
     *
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * Tap base API URL.
     *
     * @var string
     */
    protected string $tapBaseUrl = 'https://api.tap.company/v2';

    /**
     * Tap API secret key (retrieved from config).
     *
     * @var string
     */
    protected string $tapAuthSecret;

    /**
     * Tap constructor.
     *
     * Initializes Tap payment handler with required dependencies.
     *
     * @param TapService      $tapService
     * @param PaymentMethod   $paymentMethod
     * @param PaymentService  $paymentService
     * @param array           $options
     */
    public function __construct(
        TapService $tapService,
        PaymentMethod $paymentMethod,
        PaymentService $paymentService,
        $options
    ) {
        $this->paymentMethod  = $paymentMethod;
        $this->tapService     = $tapService;
        $this->paymentService = $paymentService;
        $this->tapAuthSecret  = config('services.tap.secret_test');
    }

    /**
     * Create a payment request and return redirect URL to Tap.
     *
     * @param mixed $order
     * @param mixed $user
     * @return string
     */
    public function create($order, $user): string
    {
        // Set callback URL that Tap redirects to after payment
        $data['redirect']['url'] = url(route("api.payments.callback", [$order->id]));

        // Build the payment request data using helper method
        $data = $this->paymentService->setDataPayment($order, $user);

        // Send payment request to Tap via cURL
        $response = $this->tapService->setCurl();

        // If Tap returns errors, return the response directly
        if ($response && isset($response->errors)) {
            return $response;
        }

        // Save the payment in the local database
        $payment = $this->paymentService->createPayment($order, $user, $response);

        // Store session ID and payment ID for verification later
        Session::put('transaction_id', $response['session_id']);
        Session::put('payment_id', $payment->id);

        // Return URL for client to complete the payment
        return $response->transaction->url;
    }

    /**
     * Verify the payment status after returning from Tap.
     *
     * @return Payment
     */
    public function verify(): Payment
    {
        // Get the last saved payment based on session
        $resultPayment = $this->paymentService->getPayment();

        try {
            // Send request to Tap to verify payment status
            $response = $this->checkDataPaymentCallback();

            // Handle validation errors if found
            if (isset($response->errors)) {
                return response()->json($response->errors);
            }

            // Update local payment status based on Tap response
            if ($response->status === 'CAPTURED') {
                // Mark payment as successful
                $payment = $this->paymentService->updatePayment($resultPayment, $response, 1);
            } else {
                // Mark payment as failed
                $payment = $this->paymentService->updatePayment($resultPayment, $response, -1);
            }

            // Clean up session data
            Session::forget(['payment_id', 'session_id']);

            // Return updated payment record
            return $payment;

        } catch (HttpException $ex) {
            // Catch and display Tap-related HTTP exceptions
            echo $ex->getStatusCode();
            print_r($ex->getMessage());
        }
    }
}
