<?php

/**
 * ===========================================
 *  PAYMENT HELPERS
 * ===========================================
 */

if (!function_exists('getTokenPayment')) {
    /**
     * Get authorization token for the selected payment method.
     *
     * @param string $paymentMethod
     * @return string|null
     */
    function getTokenPayment($paymentMethod)
    {
        if ($paymentMethod === 'moyasar') {
            return base64_encode(config("services.moyasar.key_live"));
        }

        if ($paymentMethod === 'tap') {
            return base64_encode(config("services.tap.key_live"));
        }

        return null;
    }
}
