<?php

namespace App\Services\Auth;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public function __construct(private SmsGateway $sms) {}

    public function issue(string $mobile): void
    {
        $recent = DB::table('otp_codes')->where('mobile', $mobile)->where('created_at', '>', now()->subMinute())->exists();
        if ($recent) {
            throw ValidationException::withMessages(['mobile' => 'برای درخواست مجدد یک دقیقه صبر کنید.']);
        }
        $code = app()->environment('production') ? (string) random_int(10000, 99999) : (string) config('services.sms.test_code', '12345');
        DB::table('otp_codes')->insert(['mobile' => $mobile, 'code_hash' => Hash::make($code), 'purpose' => 'login', 'expires_at' => now()->addMinutes(2), 'created_at' => now(), 'updated_at' => now()]);
        $this->sms->sendOtp($mobile, $code);
    }

    public function consume(string $mobile, string $code): void
    {
        $row = DB::table('otp_codes')->where('mobile', $mobile)->whereNull('consumed_at')->where('expires_at', '>', now())->latest('id')->first();
        if (! $row || $row->attempts >= 5 || ! Hash::check($code, $row->code_hash)) {
            if ($row) {
                DB::table('otp_codes')->where('id', $row->id)->increment('attempts');
            }throw ValidationException::withMessages(['code' => 'کد واردشده نادرست یا منقضی است.']);
        }
        DB::table('otp_codes')->where('id', $row->id)->update(['consumed_at' => now(), 'updated_at' => now()]);
    }
}
