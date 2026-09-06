<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Payment\RefundService;
use App\Services\Notification\NotificationService;

class ReturnRequestController extends Controller
{
    public function __construct(private RefundService $refunds, private NotificationService $notifications) {}
    public function index(Request $request)
    {
        return ReturnRequest::with(['user:id,name,mobile', 'order:id,number,payable_amount', 'items.orderItem', 'refund'])->when($request->status, fn ($query, $status) => $query->where('status', $status))->latest()->paginate(20);
    }

    public function update(Request $request, ReturnRequest $returnRequest)
    {
        $transitions = ['pending' => ['reviewing', 'approved', 'rejected'], 'reviewing' => ['approved', 'rejected'], 'approved' => ['received']];
        $data = $request->validate(['status' => 'required|in:reviewing,approved,rejected,received', 'admin_note' => 'nullable|string|max:2000', 'restock' => 'sometimes|boolean']);
        abort_unless(in_array($data['status'], $transitions[$returnRequest->status] ?? [], true), 422, 'تغییر وضعیت درخواست معتبر نیست.');
        DB::transaction(function () use ($request, $returnRequest, $data) {
            $returnRequest->update(['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
            if ($data['status'] === 'received' && ($data['restock'] ?? false)) foreach ($returnRequest->items()->with('orderItem')->get() as $item) { $variantId = $item->orderItem->variant_id; if (! $variantId) continue; $variant = ProductVariant::lockForUpdate()->find($variantId); if (! $variant) continue; $before = $variant->stock; $variant->increment('stock', $item->quantity); DB::table('inventory_movements')->insert(['variant_id' => $variant->id, 'user_id' => $request->user()->id, 'quantity' => $item->quantity, 'stock_before' => $before, 'stock_after' => $before + $item->quantity, 'reason' => 'return', 'note' => $returnRequest->number, 'created_at' => now(), 'updated_at' => now()]); }
        });
        $this->notifications->send($returnRequest->user_id, 'return_status', 'وضعیت مرجوعی تغییر کرد', 'درخواست '.$returnRequest->number.' به وضعیت '.$data['status'].' تغییر کرد.', '/profile/returns');
        return $returnRequest->fresh()->load(['user:id,name,mobile', 'order:id,number,payable_amount', 'items.orderItem']);
    }

    public function refund(Request $request, ReturnRequest $returnRequest)
    {
        abort_unless($returnRequest->status === 'received', 422, 'ابتدا دریافت کالای مرجوعی را ثبت کنید.');
        abort_if($returnRequest->refund()->exists(), 422, 'برای این درخواست قبلاً بازپرداخت ثبت شده است.');
        $suggested = (int) $returnRequest->items()->with('orderItem')->get()->sum(fn ($item) => $item->orderItem->unit_price * $item->quantity);
        $data = $request->validate(['amount' => 'nullable|integer|min:1']);
        $refund = $this->refunds->execute($returnRequest->load('order.payments'), $data['amount'] ?? $suggested, $request->user()->id);
        $this->notifications->send($returnRequest->user_id, 'refund', 'بازپرداخت ثبت شد', 'بازپرداخت درخواست '.$returnRequest->number.' برای پردازش ارسال شد.', '/profile/returns');
        return response()->json($refund, 201);
    }
}
