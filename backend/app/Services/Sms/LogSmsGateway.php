<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function sendOtp(string $mobile, string $code): void
    {
        Log::info('Development OTP', ['mobile' => $mobile, 'code' => $code]);
    }

    public function sendMessage(string $mobile, string $message): void
    {
        Log::info('Development SMS', ['mobile' => $mobile, 'message' => $message]);
    }
}
