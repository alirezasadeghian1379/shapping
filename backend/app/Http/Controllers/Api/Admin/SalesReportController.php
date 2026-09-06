<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);
        $paid = DB::table('orders')->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]);
        return [
            'summary' => ['revenue' => (int) (clone $paid)->sum('payable_amount'), 'orders' => (clone $paid)->count(), 'average_order' => (int) ((clone $paid)->avg('payable_amount') ?? 0), 'discounts' => (int) (clone $paid)->sum('discount_amount')],
            'daily' => (clone $paid)->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(payable_amount) as revenue')->groupByRaw('DATE(created_at)')->orderBy('date')->get(),
            'top_products' => DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')->where('orders.payment_status', 'paid')->whereBetween('orders.created_at', [$from, $to])->select('order_items.product_id', 'order_items.title', DB::raw('SUM(order_items.quantity) as quantity'), DB::raw('SUM(order_items.total) as revenue'))->groupBy('order_items.product_id', 'order_items.title')->orderByDesc('quantity')->limit(10)->get(),
        ];
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $orders = DB::table('orders')->leftJoin('users', 'users.id', '=', 'orders.user_id')->whereBetween('orders.created_at', [$from, $to])->select('orders.number', 'users.mobile', 'orders.status', 'orders.payment_status', 'orders.subtotal', 'orders.discount_amount', 'orders.shipping_amount', 'orders.payable_amount', 'orders.created_at')->orderBy('orders.created_at')->cursor();
        return response()->streamDownload(function () use ($orders) { $file = fopen('php://output', 'w'); fwrite($file, "\xEF\xBB\xBF"); fputcsv($file, ['شماره سفارش', 'موبایل', 'وضعیت', 'پرداخت', 'جمع کالا', 'تخفیف', 'ارسال', 'مبلغ نهایی', 'تاریخ']); foreach ($orders as $order) fputcsv($file, (array) $order); fclose($file); }, 'sales-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $request): array
    {
        $data = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        return [isset($data['from']) ? now()->parse($data['from'])->startOfDay() : now()->subDays(29)->startOfDay(), isset($data['to']) ? now()->parse($data['to'])->endOfDay() : now()->endOfDay()];
    }
}
