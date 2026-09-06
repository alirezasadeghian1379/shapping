<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function categories()
    {
        return Category::query()->whereNull('parent_id')->where('is_active', true)->with('children.children')->orderBy('sort_order')->get();
    }

    public function products(Request $r)
    {
        $query = Product::query()->where('status', 'published')->withMin(['variants as minimum_price' => fn ($q) => $q->where('is_active', true)], 'price')->with(['category', 'brand', 'variants' => fn ($q) => $q->where('is_active', true), 'media'])
            ->when($r->search, fn ($q, $v) => $q->where(fn ($q) => $q->where('title', 'like', "%$v%")->orWhere('description', 'like', "%$v%")))
            ->when($r->category, fn ($q, $v) => $q->whereHas('category', fn ($q) => $q->where('slug', $v)))
            ->when($r->brand, fn ($q, $v) => $q->whereHas('brand', fn ($q) => $q->where('slug', $v)))
            ->when($r->boolean('available'), fn ($q) => $q->whereHas('variants', fn ($q) => $q->where('stock', '>', 0)))
            ->when($r->min_price, fn ($q, $v) => $q->whereHas('variants', fn ($q) => $q->where('price', '>=', (int) $v)))
            ->when($r->max_price, fn ($q, $v) => $q->whereHas('variants', fn ($q) => $q->where('price', '<=', (int) $v)));
        foreach ((array) $r->input('attributes', []) as $key => $value) {
            $query->whereHas('variants', fn ($q) => $q->where("attributes->{$key}", $value));
        }
        match ($r->input('sort')) {
            'price_asc' => $query->orderBy('minimum_price'), 'price_desc' => $query->orderByDesc('minimum_price'),
            'popular' => $query->orderByDesc('view_count'), default => $query->latest('published_at'),
        };
        $result = $query->paginate(min($r->integer('per_page', 24), 48));
        if ($r->filled('search')) DB::table('search_logs')->insert(['user_id' => $r->user()?->id, 'query' => $r->search, 'result_count' => $result->total(), 'ip' => $r->ip(), 'created_at' => now(), 'updated_at' => now()]);
        return $result;
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->where('status', 'published')->with(['category', 'brand', 'variants', 'media'])->firstOrFail();
        $product->increment('view_count');

        return $product;
    }
}
