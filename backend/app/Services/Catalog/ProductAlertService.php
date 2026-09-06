<?php

namespace App\Services\Catalog;

use App\Contracts\SmsGateway;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductAlertService
{
    public function __construct(private SmsGateway $sms) {}

    public function process(Product $product): int
    {
        $alerts = DB::table('product_alerts')
            ->join('users', 'users.id', '=', 'product_alerts.user_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'product_alerts.variant_id')
            ->where('product_alerts.product_id', $product->id)
            ->whereNull('product_alerts.notified_at')
            ->select('product_alerts.*', 'users.mobile', 'product_variants.price', 'product_variants.stock')
            ->get();

        $sent = 0;
        foreach ($alerts as $alert) {
            $matched = $alert->type === 'price_drop'
                ? $alert->price !== null && $alert->target_price !== null && $alert->price <= $alert->target_price
                : $alert->stock !== null && $alert->stock > 0;

            if (! $matched) continue;

            $message = $alert->type === 'price_drop'
                ? "قیمت «{$product->title}» به محدوده دلخواه شما رسید."
                : "محصول «{$product->title}» دوباره موجود شد.";
            $this->sms->sendMessage($alert->mobile, $message);
            DB::table('product_alerts')->where('id', $alert->id)->update(['notified_at' => now(), 'updated_at' => now()]);
            $sent++;
        }

        return $sent;
    }
}
