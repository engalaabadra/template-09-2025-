<?php

namespace App\Services\User\Payment;

use Illuminate\Support\Facades\Session;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\User\PaymentGateways\PaymentGatewayFactory\PaymentGatewayFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;

/**
 * PaymentService
 *
 * This service handles operations related to retrieving, creating,
 * and updating payment records in the system.
 * 
 * @package App\Services\User\Payment
 * 
 */
class PaymentService
{
    /**
     * Retrieve the payment method, payment record, and transaction ID from session.
     *
     * @return array|int Returns array of payment data or 404 if payment not found
     */
    public function getPayment()
    {
        // Get payment method slug from session
        $paymentMethodSlug = Session::get('payment_method_id');

        // Find the payment method using the slug
        $paymentMethod = PaymentMethod::where('slug', $paymentMethodSlug)->first();

        // Return error if payment method not found
        if (!$paymentMethod) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.validation_failed'), [
                'slug' => 'validation.this slug payment method not found , pls select again'
            ]);
        }

        // Get transaction ID from session
        $transactionId = Session::get('transaction_id');

        // Find the payment by transaction ID
        $payment = Payment::where(['transaction_id' => $transactionId])->first();

        // Return 404 if payment not found
        if (!$payment) return 404;

        // Return the payment data
        return [
            'payment_method' => $paymentMethod,
            'payment'        => $payment,
            'transaction_id' => $transactionId
        ];
    }

    /**
     * Create a new payment record based on the order and response.
     *
     * @param mixed $order    The order model instance
     * @param mixed $user     The user making the payment
     * @param mixed $response The payment gateway response
     * @return Payment        The created payment model
     */
    public function createPayment($order, $user, $response)
    {
        // Create new payment record
        $payment = Payment::create([
            // 'payment_method_id' => $this->paymentMethod->id, // Optional if needed
            'paymentable_id'     => $order->id,
            'paymentable_type'   => get_class($order),
            'payer_id'           => $user->id,
            'payer_type'         => get_class($user),
            'amount'             => $order->total,
            'currency_code'      => $order->currency_code,
            'type'               => 'payment',
            'status'             => 0,
            'transaction_id'     => $response->transaction->id,
            'payment_response'   => $response->result
        ]);

        // Return created payment
        return $payment;
    }

    /**
     * Update the status of a payment record by transaction ID and payment method ID.
     *
     * @param string  $id              Transaction ID
     * @param int     $paymentMethodId Payment method ID
     * @param int     $status          New status to set
     * @return Payment|null            Updated payment record
     */
    public function updatePayment($id, $paymentMethodId, $status)
    {
        // Find the payment by transaction ID and payment method ID
        $payment = Payment::where('transaction_id', $id)
                          ->where('payment_method_id', $paymentMethodId)
                          ->first();

        // Update status
        $payment['status'] = $status;

        // Save changes
        $payment->save();

        // Return updated payment
        return $payment;
    }
}
