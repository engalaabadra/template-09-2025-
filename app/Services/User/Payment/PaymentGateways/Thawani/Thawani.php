<?php

namespace App\Services\User\Payment\PaymentGateways\Thawani;

use Carbon\Exceptions\Exception;
use Illuminate\Support\Facades\Session;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Twilio\Exceptions\HttpException;
use App\Services\User\Payment\PaymentGateways\PaymentGatewayInterface;

/**
 * Class Thawani
 *
 * This class handles integration with Thawani payment gateway,
 * providing methods to create a checkout session and verify payment status.
 * 
 * @package App\Services\User\Payment\PaymentGateways\Thawani
 * 
 */
class Thawani implements PaymentGatewayInterface
{
    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var ThawaniService
     */
    protected $thawaniService;

    /**
     * @var mixed
     */
    protected $options;

    /**
     * @var mixed
     */
    protected $client;

    /**
     * Thawani constructor.
     *
     * @param PaymentMethod   $paymentMethod
     * @param PaymentService  $paymentService
     * @param ThawaniService  $thawaniService
     * @param mixed           $options
     */
    public function __construct(
        PaymentMethod $paymentMethod,
        PaymentService $paymentService,
        ThawaniService $thawaniService,
        $options
    ) {
        $this->paymentMethod   = $paymentMethod;
        $this->paymentService  = $paymentService;
        $this->thawaniService  = $thawaniService;
        $this->options         = $options;
    }

    /**
     * Create payment checkout session
     *
     * @param mixed $order
     * @param mixed $user
     * @return string
     */
    public function create($order, $user): string
    {
        // Prepare payment data using payment service
        $data = $this->paymentService->setDataPayment($order);

        try {
            // Call Thawani client to create a checkout session
            $response = $this->client->createCheckoutSession($data);

            // Store payment info in the database
            $payment = $this->paymentService->createPayment($order, $user, $response);

            // Store session ID and payment ID in session
            Session::put('transaction_id', $response['session_id']);
            Session::put('payment_id', $payment->id);

            // Return the payment URL
            return $response['session_url'] ?? '';
        } catch (Exception $ex) {
            // Handle exception during payment creation
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    /**
     * Verify payment status after redirection
     *
     * @return Payment
     */
    public function verify(): Payment
    {
        // Retrieve the payment from session
        $resultPayment = $this->paymentService->getPayment();

        try {
            // Get transaction ID from session
            $transaction_id = Session::get('transaction_id');

            // Get payment session info from Thawani
            $response = $this->client->getCheckoutSession($transaction_id);

            // Check the payment status
            if ($response['data']['payment_status'] === 'paid') {
                // Update payment as successful
                $payment = $this->paymentService->updatePayment($resultPayment, $response, 1);
            } elseif ($response['data']['payment_status'] === 'faild') {
                // Update payment as failed
                $payment = $this->paymentService->updatePayment($resultPayment, $response, -1);
            }

            // Forget session data
            Session::forget(['payment_id', 'session_id']);

            // Return the updated payment
            return $payment;
        } catch (HttpException $ex) {
            // Handle exception during payment verification
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }
}
