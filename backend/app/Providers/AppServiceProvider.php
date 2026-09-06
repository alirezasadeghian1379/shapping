<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Contracts\SmsGateway;
use App\Services\Payment\ZarinpalGateway;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Support\ServiceProvider;
use App\Models\OrderItem;
use App\Services\Catalog\PricingService;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, LogSmsGateway::class);
        $this->app->bind(PaymentGateway::class, ZarinpalGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        OrderItem::created(function (OrderItem $item) {
            if (! $item->variant_id) return;
            $campaignItem = app(PricingService::class)->activeItem($item->variant_id);
            if ($campaignItem) DB::table('sale_campaign_items')->where('id', $campaignItem->id)->increment('sold', $item->quantity, ['updated_at' => now()]);
        });
    }
}
