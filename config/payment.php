<?php

use App\Services\Payment\PaystackPayment;
use App\Services\Payment\PaymentGatewayInterface;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Gateway
    |--------------------------------------------------------------------------
    |
    | The gateway used when no explicit gateway is passed in the URL
    | (e.g. /payment/push without a {gateway} segment resolves to this).
    |
    */

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'paystack'),

    /*
    |--------------------------------------------------------------------------
    | Registered Gateways
    |--------------------------------------------------------------------------
    |
    | Map of gateway name => implementation class.
    | The PaymentGatewayManager resolves these out of the container, so each
    | class is constructor-injected (which means each gateway can declare its
    | own dependencies and Laravel will wire them automatically).
    |
    | Adding a new gateway = (1) write a class implementing
    | PaymentGatewayInterface, (2) add one entry to this map, (3) add one
    | line to PaymentGatewayManager::__construct().
    |
    */

    'gateways' => [
        'paystack' => PaystackPayment::class,
        // 'stripe' => \App\Services\Payment\StripePayment::class,
        // 'bkash'  => \App\Services\Payment\BkashPayment::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateway-Specific Settings
    |--------------------------------------------------------------------------
    |
    | Each gateway block holds its own secrets. They're separate from
    | config/services.php so the payment subsystem is self-contained.
    |
    | IMPORTANT: never call url() or route() inside this file — config is
    | loaded during console bootstrap (e.g. `php artisan migrate`), when no
    | HTTP request exists. Resolve URLs lazily inside gateway methods.
    |
    */

    'paystack' => [
        'secret'        => env('PAYSTACK_SECRET_KEY'),
        'public'        => env('PAYSTACK_PUBLIC_KEY'),
        'base_url'      => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'callback_url'  => env('PAYSTACK_CALLBACK_URL'),
    ],

    // 'stripe' => [
    //     'secret'        => env('STRIPE_SECRET_KEY'),
    //     'public'        => env('STRIPE_PUBLIC_KEY'),
    //     'webhook_secret'=> env('STRIPE_WEBHOOK_SECRET'),
    //     'base_url'      => 'https://api.stripe.com',
    // ],
];