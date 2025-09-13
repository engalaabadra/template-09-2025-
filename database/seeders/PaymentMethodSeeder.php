<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

/**
 * Class PaymentMethodTableSeeder.
 */
class PaymentMethodSeeder extends Seeder
{

    /**
     * Run the database seed.
     */
    public function run(): void
    {

        PaymentMethod::create([
            'name' => trans('modules/payments/seeders.Card')
        ]);
        PaymentMethod::create([
            'name' => trans('modules/payments/seeders.PayPal')
        ]);
        PaymentMethod::create([
            'name' => trans('modules/payments/seeders.Google Pay')
        ]);
        PaymentMethod::create([
            'name' => trans('modules/payments/seeders.Apple Pay')
        ]);
        PaymentMethod::create([
            'name' => trans('modules/payments/seeders.Wallet')
        ]);
    }
}
