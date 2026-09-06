<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Refund;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\DB;
use Throwable;

class RefundService
{
    public function __construct(private PaymentGateway $gateway) {}

    public function execute(ReturnRequest $return, int $amount, int $adminId): Refund
    {
        $payment = $return->order->payments()->where('status', 'paid')->latest()->firstOrFail();
        abort_if($amount > $payment->amount || $amount < 1, 422, 'مبلغ بازپرداخت معتبر نیست.');
        $refund = Refund::create(['return_request_id' => $return->id, 'payment_id' => $payment->id, 'created_by' => $adminId, 'gateway' => $payment->gateway, 'amount' => $amount]);
        try {
            $result = $this->gateway->refund($payment, $amount, 'بازپرداخت درخواست '.$return->number);
            DB::transaction(function () use ($return, $refund, $result, $amount, $payment) {
                $refund->update(['status' => $result['status'], 'reference_id' => $result['reference_id'] ?? null, 'response_payload' => $result, 'processed_at' => now()]);
                $return->update(['status' => $result['status'] === 'succeeded' ? 'refunded' : 'refund_processing', 'reviewed_at' => now()]);
                $totalRefunded = Refund::where('payment_id', $payment->id)->whereIn('status', ['succeeded', 'processing'])->sum('amount');
                $return->order()->update(['status' => 'returned', 'payment_status' => $totalRefunded >= $payment->amount ? 'refunded' : 'partially_refunded']);
            });
            return $refund->fresh();
        } catch (Throwable $exception) {
            $refund->update(['status' => 'failed', 'failure_message' => $exception->getMessage(), 'processed_at' => now()]);
            throw $exception;
        }
    }
}
