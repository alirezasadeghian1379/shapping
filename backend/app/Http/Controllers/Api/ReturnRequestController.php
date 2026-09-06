<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        return ReturnRequest::where('user_id', $request->user()->id)->with(['order:id,number', 'items.orderItem'])->latest()->paginate(15);
    }

    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->status === 'delivered', 422, 'فقط سفارش تحویل‌شده قابل مرجوعی است.');
        abort_if($order->updated_at->lt(now()->subDays(7)), 422, 'مهلت هفت‌روزه مرجوعی تمام شده است.');
        abort_if(ReturnRequest::where('order_id', $order->id)->whereNotIn('status', ['rejected', 'cancelled'])->exists(), 422, 'برای این سفارش یک درخواست فعال وجود دارد.');
        $data = $request->validate(['reason' => 'required|in:damaged,wrong_item,not_as_described,quality,other', 'description' => 'nullable|required_if:reason,other|string|max:2000', 'items' => 'required|array|min:1', 'items.*.order_item_id' => 'required|distinct|exists:order_items,id', 'items.*.quantity' => 'required|integer|min:1', 'items.*.condition' => 'required|in:unopened,opened,damaged']);
        $orderItems = $order->items->keyBy('id');
        foreach ($data['items'] as $item) if (! $orderItems->has($item['order_item_id']) || $item['quantity'] > $orderItems[$item['order_item_id']]->quantity) throw ValidationException::withMessages(['items' => 'اقلام یا تعداد انتخاب‌شده معتبر نیست.']);
        $return = DB::transaction(function () use ($request, $order, $data) { $return = ReturnRequest::create(['order_id' => $order->id, 'user_id' => $request->user()->id, 'number' => 'RET-'.strtoupper(Str::random(10)), 'reason' => $data['reason'], 'description' => $data['description'] ?? null]); $return->items()->createMany($data['items']); return $return; });
        return response()->json($return->load('items.orderItem'), 201);
    }
}
