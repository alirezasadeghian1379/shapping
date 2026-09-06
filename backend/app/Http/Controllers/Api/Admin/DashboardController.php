<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return ['users' => User::count(), 'products' => Product::count(), 'pending_orders' => Order::where('status', 'pending')->count(), 'today_sales' => (int) Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('payable_amount'), 'low_stock' => DB::table('product_variants')->whereColumn('stock', '<=', 'low_stock_threshold')->count()];
    }
}
