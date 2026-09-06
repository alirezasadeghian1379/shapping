<?php

namespace App\Contracts;

use App\Models\Payment;

interface PaymentGateway
{
    public function request(Payment $payment, string $callbackUrl): string;

    public function verify(Payment $payment, array $callback): string;

    public function refund(Payment $payment, int $amount, string $description): array;
}
