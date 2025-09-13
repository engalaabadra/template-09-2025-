<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use App\Models\Reservation;

/**
 * Class PaymentMethodController
 *
 * This controller handles the payment process for a reservation.
 * Specifically, it renders the payment form with the necessary data.
 */
class PaymentMethodController extends Controller
{
    /**
     * Finalize the payment process by returning the payment form view.
     *
     * @param float $price          The price to be paid.
     * @param int   $reservationId  The ID of the reservation.
     * @param mixed $user           The user initiating the payment.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function paymentProccessFinishing($price, $reservationId, $user)
    {
        // Find the reservation by its ID
        $reservation = Reservation::where('id', $reservationId)->first();

        // If the reservation is not found, return a 404 error response
        if (!$reservation) {
            return abort(404);
        }

        // Render the payment form view and pass the required data to it
        $resultForm = view('payments.form')->with(compact('price', 'reservationId', 'user'));

        return $resultForm;
    }
}
