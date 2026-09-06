<?php

namespace App\Contracts;

interface SmsGateway
{
    public function sendOtp(string $mobile, string $code): void;

    public function sendMessage(string $mobile, string $message): void;
}
