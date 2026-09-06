<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZarinpalGateway implements PaymentGateway
{
    public function request(Payment $payment, string $callbackUrl): string
    {
        $base = config('services.zarinpal.sandbox') ? 'https://sandbox.zarinpal.com/pg/v4/payment' : 'https://api.zarinpal.com/pg/v4/payment';
        $payload = ['merchant_id' => config('services.zarinpal.merchant_id'), 'amount' => $payment->amount * 10, 'callback_url' => $callbackUrl, 'description' => 'پرداخت سفارش '.$payment->order_id];
        $result = Http::post($base.'/request.json', $payload)->throw()->json();
        $authority = data_get($result, 'data.authority');
        if (! $authority) {
            throw new RuntimeException(data_get($result, 'errors.message', 'خطا در ایجاد پرداخت'));
        }
        $payment->update(['authority' => $authority, 'request_payload' => $payload, 'response_payload' => $result]);

        return (config('services.zarinpal.sandbox') ? 'https://sandbox.zarinpal.com' : 'https://www.zarinpal.com').'/pg/StartPay/'.$authority;
    }

    public function verify(Payment $payment, array $callback): string
    {
        abort_unless(($callback['Status'] ?? '') === 'OK', 422, 'پرداخت توسط کاربر لغو شد.');
        $base = config('services.zarinpal.sandbox') ? 'https://sandbox.zarinpal.com/pg/v4/payment' : 'https://api.zarinpal.com/pg/v4/payment';
        $result = Http::post($base.'/verify.json', ['merchant_id' => config('services.zarinpal.merchant_id'), 'amount' => $payment->amount * 10, 'authority' => $payment->authority])->throw()->json();
        $ref = (string) data_get($result, 'data.ref_id');
        if (! $ref) {
            throw new RuntimeException(data_get($result, 'errors.message', 'تأیید پرداخت ناموفق بود'));
        }
        $payment->update(['status' => 'paid', 'reference_id' => $ref, 'response_payload' => $result, 'paid_at' => now()]);

        return $ref;
    }

    public function refund(Payment $payment, int $amount, string $description): array
    {
        if (config('services.zarinpal.sandbox')) {
            return ['reference_id' => 'SANDBOX-REFUND-'.strtoupper(str()->random(12)), 'status' => 'succeeded', 'sandbox' => true];
        }
        $token = config('services.zarinpal.access_token');
        if (! $token) throw new RuntimeException('توکن دسترسی زرین‌پال برای بازپرداخت تنظیم نشده است.');
        $query = <<<'GRAPHQL'
mutation AddRefund($session_id: ID!, $amount: BigInteger!, $description: String, $reason: RefundReasonEnum) {
  resource: AddRefund(session_id: $session_id, amount: $amount, description: $description, reason: $reason) {
    id amount terminal_id timeline { refund_amount refund_time refund_status }
  }
}
GRAPHQL;
        $result = Http::withToken($token)->post('https://next.zarinpal.com/api/v4/graphql/', ['query' => $query, 'variables' => ['session_id' => $payment->authority, 'amount' => $amount * 10, 'description' => $description, 'reason' => 'CUSTOMER_REQUEST']])->throw()->json();
        if (data_get($result, 'errors.0.message')) throw new RuntimeException(data_get($result, 'errors.0.message'));
        $reference = (string) data_get($result, 'data.resource.id');
        if (! $reference) throw new RuntimeException('پاسخ بازپرداخت زرین‌پال معتبر نیست.');
        return ['reference_id' => $reference, 'status' => 'processing', 'response' => $result];
    }
}
