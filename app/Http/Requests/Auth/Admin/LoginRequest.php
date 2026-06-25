<?php

namespace App\Http\Requests\Auth\Admin;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;

/**
 * Class LoginRequest
 *
 * Handles validation and authentication logic for login requests.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to submit login requests
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Email is required if phone number is not provided, must exist in users table
            'email'       => ['nullable', 'required_without:phone_no', 'email', 'exists:users,email'],

            // Country ID is required if phone number is provided
            'country_id'  => ['required_with:phone_no', 'numeric'],

            // Phone number is required if email is not provided, must be numeric and valid length
            'phone_no'    => ['nullable', 'required_without:email', 'numeric', 'regex:/^\d+$/', 'digits_between:7,14', 'exists:users,phone_no'],

            // Password is always required and must follow default password rules
            'password'    => ['required', Rules\Password::defaults()],

            // FCM token is optional
            'fcm_token'   => ['sometimes'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // Ensure user is not rate limited
        $this->ensureIsNotRateLimited();

        // Attempt to login using email and password
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {

            // If failed, increment rate limit
            RateLimiter::hit($this->throttleKey());

            // Throw validation exception with failure message
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // If login succeeds, reset the rate limit
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // If the user has not exceeded rate limit, proceed
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Trigger lockout event
        event(new Lockout($this));

        // Get number of seconds remaining before next attempt
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Throw validation error with throttle message
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        // Create a unique throttle key using email and IP
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
