<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Contracts\SmsGateway;
use App\Services\Notification\NotificationService;

class OrderController extends Controller
{
    public function __construct(private SmsGateway $sms, private NotificationService $notifications) {}
    public function index(Request $request)
    {
        return Order::query()->with('user:id,name,mobile')->withCount('items')->when($request->status, fn ($query, $status) => $query->where('status', $status))->when($request->search, fn ($query, $search) => $query->where(fn ($query) => $query->where('number', 'like', "%{$search}%")->orWhereHas('user', fn ($query) => $query->where('mobile', 'like', "%{$search}%"))))->latest()->paginate(min($request->integer('per_page', 20), 100));
    }

    public function show(Order $order): Order
    {
        return $order->load(['user', 'items', 'payments', 'shipment.events'])->setRelation('status_histories', collect(DB::table('order_status_histories')->where('order_id', $order->id)->orderBy('created_at')->get()));
    }

    public function updateStatus(Request $request, Order $order): Order
    {
        $data = $request->validate(['status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned', 'note' => 'nullable|string|max:500']);
        abort_if(in_array($order->status, ['delivered', 'cancelled'], true), 422, 'سفارش نهایی‌شده قابل تغییر نیست.');
        $from = $order->status;
        $order->update(['status' => $data['status']]);
        DB::table('order_status_histories')->insert(['order_id' => $order->id, 'user_id' => $request->user()->id, 'from_status' => $from, 'to_status' => $data['status'], 'note' => $data['note'] ?? null, 'created_at' => now(), 'updated_at' => now()]);
        $this->notifications->send($order->user_id, 'order_status', 'وضعیت سفارش تغییر کرد', 'وضعیت سفارش شما به '.$data['status'].' تغییر کرد.', '/profile/orders/'.$order->id, ['status' => $data['status']]);

        return $order->fresh();
    }

    public function shipment(Request $request, Order $order)
    {
        $data = $request->validate(['carrier' => 'required|string|max:100', 'tracking_code' => 'nullable|string|max:100', 'tracking_url' => 'nullable|url|max:500', 'status' => 'required|in:preparing,shipped,in_transit,out_for_delivery,delivered,failed,returned', 'note' => 'nullable|string|max:1000']);
        $shipment = $order->shipment()->updateOrCreate([], $data + ['shipped_at' => in_array($data['status'], ['shipped','in_transit','out_for_delivery','delivered'], true) ? ($order->shipment?->shipped_at ?? now()) : null, 'delivered_at' => $data['status'] === 'delivered' ? now() : null]);
        $orderStatus = $data['status'] === 'delivered' ? 'delivered' : ($data['status'] === 'shipped' ? 'shipped' : null);
        if ($orderStatus && $order->status !== $orderStatus) { $from = $order->status; $order->update(['status' => $orderStatus]); DB::table('order_status_histories')->insert(['order_id' => $order->id, 'user_id' => $request->user()->id, 'from_status' => $from, 'to_status' => $orderStatus, 'note' => $orderStatus === 'shipped' ? 'تحویل به '.$data['carrier'] : 'تحویل سفارش به مشتری', 'created_at' => now(), 'updated_at' => now()]); }
        if ($data['tracking_code']) $this->sms->sendMessage($order->user->mobile, 'سفارش شما ارسال شد. کد رهگیری: '.$data['tracking_code']);
        if ($data['tracking_code']) $this->notifications->send($order->user_id, 'shipment', 'سفارش شما ارسال شد', 'کد رهگیری مرسوله: '.$data['tracking_code'], '/profile/orders/'.$order->id);
        return $shipment->load('events');
    }

    public function shipmentEvent(Request $request, Order $order)
    {
        $shipment = $order->shipment()->firstOrFail();
        $data = $request->validate(['status' => 'required|in:preparing,shipped,in_transit,out_for_delivery,delivered,failed,returned', 'title' => 'required|string|max:200', 'location' => 'nullable|string|max:200', 'description' => 'nullable|string|max:1000', 'occurred_at' => 'nullable|date']);
        $event = $shipment->events()->create($data + ['occurred_at' => $data['occurred_at'] ?? now()]);
        $shipment->update(['status' => $data['status'], 'delivered_at' => $data['status'] === 'delivered' ? now() : $shipment->delivered_at]);
        return response()->json($event, 201);
    }
}
