<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    public function __construct(PaystackPayment $paystack)
    {
        // Register every supported gateway here. Adding a new gateway
        // later = (a) write the class, (b) add one line here.
        $this->register('paystack', $paystack);
    }

    public function register(string $name, PaymentGatewayInterface $gateway): static
    {
        $this->gateways[$name] = $gateway;
        return $this;
    }

    public function gateway(string $name): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Payment gateway [{$name}] is not registered.");
        }
        return $this->gateways[$name];
    }

    public function default(): PaymentGatewayInterface
    {
        return $this->gateway(config('payment.default', 'paystack'));
    }

    /** @return string[] */
    public function available(): array
    {
        return array_keys($this->gateways);
    }
}