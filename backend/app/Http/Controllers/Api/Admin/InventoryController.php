<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\Catalog\ProductAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(private ProductAlertService $alerts) {}

    public function index(Request $request)
    {
        return ProductVariant::query()->with('product:id,title,slug')
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereColumn('stock', '<=', 'low_stock_threshold'))
            ->when($request->search, fn ($query, $search) => $query->where(fn ($query) => $query->where('sku', 'like', "%{$search}%")->orWhereHas('product', fn ($query) => $query->where('title', 'like', "%{$search}%"))))
            ->orderBy('stock')->paginate(min($request->integer('per_page', 30), 100));
    }

    public function adjust(Request $request, ProductVariant $variant)
    {
        $data = $request->validate(['quantity' => 'required|integer|between:-100000,100000|not_in:0', 'reason' => 'required|in:purchase,correction,return,damage,manual', 'note' => 'nullable|string|max:500']);
        DB::transaction(function () use ($request, $variant, $data) {
            $locked = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
            $before = $locked->stock;
            $after = $before + $data['quantity'];
            abort_if($after < 0, 422, 'موجودی نمی‌تواند منفی شود.');
            $locked->update(['stock' => $after]);
            DB::table('inventory_movements')->insert(['variant_id' => $variant->id, 'user_id' => $request->user()->id, 'quantity' => $data['quantity'], 'stock_before' => $before, 'stock_after' => $after, 'reason' => $data['reason'], 'note' => $data['note'] ?? null, 'created_at' => now(), 'updated_at' => now()]);
        });
        $this->alerts->process($variant->product);
        return $variant->fresh()->load('product:id,title,slug');
    }

    public function movements(ProductVariant $variant)
    {
        return DB::table('inventory_movements')->leftJoin('users', 'users.id', '=', 'inventory_movements.user_id')->where('variant_id', $variant->id)->select('inventory_movements.*', 'users.name as user_name')->latest('inventory_movements.id')->paginate(30);
    }
}
