<?php

namespace App\Services\User\Payment\PaymentGateways\Paypal;

use Carbon\Exceptions\Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentGateways\PaymentGatewayInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\User\Payment\PaymentService;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

/**
 * Class Paypal
 *
 * This class handles PayPal payment gateway operations like creating and verifying payments.
 * It implements the PaymentGatewayInterface to ensure consistency across different gateways.
 * 
 * @package App\Services\User\Payment\PaymentGateways\Paypal
 * 
 */
class Paypal implements PaymentGatewayInterface
{
    protected $paymentMethod;

    public PaypalService $paypalService;

    public $paymentService;

    public $client;

    /**
     * Constructor: Initialize PayPal service, payment service, and client.
     */
    public function __construct()
    {
        // Fetch PayPal configuration from the database
        $this->paymentMethod = PaymentMethod::where('slug', 'paypal')->first();

        // Initialize PayPal service
        $this->paypalService = new PaypalService();

        // Initialize generic payment service
        $this->paymentService = new PaymentService();

        // Get PayPal API client
        $this->client = $this->paypalService->client();
    }

    /**
     * Create a PayPal payment and redirect the user to PayPal approval page.
     *
     * @param mixed $order
     * @param mixed $user
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function create($order, $user)
    {
        // Set up PayPal payment data
        $data = $this->paypalService->setDataPayment($order);

        try {
            // Execute payment creation
            $response = $this->client->execute($data);

            // Loop through PayPal response links to find the approval link
            foreach ($response->result->links as $link) {
                if ($link->rel == 'approve') {

                    // Optional: You can store session or create payment record here
                    // $payment = $this->paymentService->createPayment($order , $user , $response);
                    // Session::put('transaction_id', $response['session_id']);
                    // Session::put('payment_id', $payment->id);

                    // Redirect user to PayPal for approval
                    return redirect()->away($link->href); // External redirect
                }
            }

        } catch (Exception $ex) {
            // Handle exceptions and log the error
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    /**
     * Verify the PayPal payment after user returns from PayPal.
     *
     * @param string $id
     * @return Payment|null
     */
    public function verify($id): Payment
    {
        // Retrieve the stored payment
        $resultPayment = $this->paymentService->getPayment();

        // Create a capture request with PayPal
        $request = new OrdersCaptureRequest($id);
        $request->prefer('return=representation');

        try {
            // Execute the capture request
            $response = $this->client->execute($request);

            // Update the payment based on PayPal status
            if ($response->result->status == 'COMPLETED') {
                $payment = $this->paymentService->updatePayment($id, $paymentMethodId, $status = 1);
            } elseif ($response->result->status == 'CANCELED') {
                $payment = $this->paymentService->updatePayment($id, $paymentMethodId, $status = -1);
            }

            // Clear session data
            Session::forget(['payment_id', 'session_id']);

            return $payment;

        } catch (HttpException $ex) {
            // Handle exceptions and log the error
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    /**
     * Define the form options required to configure the PayPal gateway.
     *
     * @return array
     */
    public function formOptions(): array
    {
        return [
            'client_id' => [
                'type'         => 'text',
                'label'        => 'Client ID',
                'placeholder'  => 'Client ID',
                'required'     => 'true',
                'validation'   => 'required|string|max:225',
            ],
            'client_secret' => [
                'type'         => 'text',
                'label'        => 'Client Secret',
                'placeholder'  => 'Client Secret',
                'required'     => 'true',
                'validation'   => 'required|string|max:225',
            ]
        ];
    }
}
