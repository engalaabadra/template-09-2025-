<?php

namespace App\Services\User\Payment\PaymentGateways\Tap;

use Illuminate\Support\Facades\Http;

/**
 * TapService handles interactions with the Tap Payment Gateway.
 * It supports payment creation, verification, and status checking.
 * 
 * @package App\Services\User\Payment\PaymentGateways\Tap
 * 
 */
class TapService
{
    /**
     * Return Tap HTTP client instance.
     *
     * @return mixed
     */
    protected function client()
    {
        // If client is not already initialized, create it
        if (!$this->client()) {
            $client = new TapHttpClient(
                new SandboxEnvironment(
                    $this->paymentMethod->options['client_id'],
                    $this->paymentMethod->options['client_secert'],
                )
            );
        }

        // Return client instance
        return $this->client;
    }

    /**
     * Set payment data payload for Tap API.
     *
     * @param mixed $order
     * @param mixed $user
     * @return array
     */
    protected function setDataPayment($order, $user): array
    {
        // Set basic payment data
        $data['amount'] = $order->price;
        $data['currency'] = systemCurrency();

        // Set customer first name
        $data['customer']['first_name'] = $user ? $user->full_name : null;

        // Set email or generate fallback
        $email = $user ? $user->email : null;
        if (!$email) {
            $email = $user ? $user->full_name . ' #' . $order->id . '@template.net' : null;
        }
        $data['customer']['email'] = $email;

        // Set customer phone number
        $data['customer']['phone']['number'] = $user ? $user->phone_no : null;

        // Specify source ID for Tap
        $data['source']['id'] = "src_all";

        return $data;
    }

    /**
     * Send payment request to Tap using cURL.
     *
     * @return mixed
     */
    protected function setCurl()
    {
        // Set request headers
        $headers = [
            "Content-Type:application/json",
            config('services.tap.secret_test'),
        ];

        // Initialize cURL session
        $ch = curl_init();
        $url = "https://api.tap.company/v2/charges";

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // $data must be defined before
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute request
        $output = curl_exec($ch);
        curl_close($ch);

        // Decode and return response
        $response = json_decode($output);
        return $response;
    }

    /**
     * Verify payment data via callback using Tap API.
     *
     * @return object
     */
    protected function checkDataPaymentCallback(): object
    {
        // Set authorization headers
        $headers = [
            "Content-Type: application/json",
            "Authorization: Basic {$this->tapAuthSecret}",
        ];

        // Build Tap charge URL
        $url = "{$this->tapBaseUrl}/charges/" . $this->getTapId();

        // Send GET request using internal curl method
        $output = $this->curl($url, 'GET', $headers);

        // Decode and return result or errors
        $response = json_decode($output);
        if ($response && isset($response->errors)) {
            return $response->errors;
        }

        return $response;
    }

    /**
     * Get Tap charge status by ID.
     *
     * @param string $tapId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatusTap(string $tapId): \Illuminate\Http\JsonResponse
    {
        // Build charge status URL
        $url = "{$this->tapBaseUrl}/charges/{$tapId}";

        // Set headers
        $headers = [
            "Authorization: Basic {$this->tapAuthSecret}",
            'Accept: application/json',
        ];

        // Execute curl call
        $data = $this->curl($url, 'GET', $headers);

        // Decode response and get status
        $status = json_decode($data)->status;

        // Return status as JSON response
        return response()->json(['status' => $status === 'CAPTURED']);
    }

    /**
     * Generic curl wrapper to handle HTTP requests.
     *
     * @param string $url
     * @param string $method
     * @param array|null $header
     * @param mixed $postdata
     * @param int $timeout
     * @return mixed
     */
    public function curl($url, $method = 'get', $header = null, $postdata = null, $timeout = 60)
    {
        // Initialize cURL
        $s = curl_init();

        // Set basic options
        curl_setopt($s, CURLOPT_URL, $url);
        if ($header) curl_setopt($s, CURLOPT_HTTPHEADER, $header);
        curl_setopt($s, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($s, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($s, CURLOPT_MAXREDIRS, 3);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($s, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($s, CURLOPT_COOKIEFILE, 'cookie.txt');

        // Set request method
        if (strtolower($method) === 'post') {
            curl_setopt($s, CURLOPT_POST, true);
            curl_setopt($s, CURLOPT_POSTFIELDS, $postdata);
        } elseif (strtolower($method) === 'delete') {
            curl_setopt($s, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif (strtolower($method) === 'put') {
            curl_setopt($s, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($s, CURLOPT_POSTFIELDS, $postdata);
        }

        // Set additional headers
        curl_setopt($s, CURLOPT_HEADER, 0);
        curl_setopt($s, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1');
        curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);

        // Execute and capture output
        $html = curl_exec($s);
        $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
        curl_close($s);

        // Return raw HTML response
        return $html;
    }

    /**
     * Capture a Moyasar payment using the payment ID and token.
     *
     * @param string $id
     * @param string $token
     * @return array
     */
    public function getCapturePayment(string $id, string $token): array
    {
        // Send POST request using Laravel HTTP client
        $response = Http::baseUrl($this->moyasarBaseUrl)
            ->withHeaders([
                'Authorization' => "Basic {$token}",
            ])
            ->post("payments/{$id}/capture")
            ->json();

        return $response;
    }
}
