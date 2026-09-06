<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductVariant extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['attributes' => 'array', 'dimensions' => 'array', 'is_active' => 'boolean'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPriceAttribute($value): int
    {
        if (! $this->exists) return (int) $value;
        $sale = DB::table('sale_campaign_items as item')->join('sale_campaigns as campaign', 'campaign.id', '=', 'item.campaign_id')->where('item.variant_id', $this->id)->where('campaign.is_active', true)->where('campaign.starts_at', '<=', now())->where('campaign.ends_at', '>=', now())->where(fn ($query) => $query->whereNull('item.stock_limit')->orWhereColumn('item.sold', '<', 'item.stock_limit'))->min('item.sale_price');
        return (int) ($sale ?? $value);
    }
}
