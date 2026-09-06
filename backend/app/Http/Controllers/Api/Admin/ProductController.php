<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\Catalog\ProductAlertService;

class ProductController extends Controller
{
    public function __construct(private ProductAlertService $alerts) {}
    public function index(Request $request) { return Product::with(['category', 'brand', 'variants', 'media'])->when($request->search, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))->latest()->paginate(min($request->integer('per_page', 20), 100)); }
    public function store(Request $request) { return response()->json($this->persist($request, new Product), 201); }
    public function show(Product $product) { return $product->load(['category', 'brand', 'variants', 'media']); }
    public function update(Request $request, Product $product) { return $this->persist($request, $product); }
    public function destroy(Product $product) { $product->delete(); return response()->noContent(); }

    private function persist(Request $request, Product $product): Product
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id', 'brand_id' => 'nullable|exists:brands,id', 'title' => 'required|string|max:200',
            'slug' => ['required', 'alpha_dash', Rule::unique('products')->ignore($product)], 'product_type' => 'sometimes|in:physical,digital',
            'short_description' => 'nullable|string', 'description' => 'nullable|string', 'status' => 'sometimes|in:draft,published,archived',
            'is_featured' => 'sometimes|boolean', 'seo' => 'nullable|array', 'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id', 'variants.*.sku' => 'required|string', 'variants.*.price' => 'required|integer|min:0',
            'variants.*.compare_at_price' => 'nullable|integer|min:0', 'variants.*.stock' => 'required|integer|min:0',
            'variants.*.attributes' => 'required|array', 'variants.*.is_active' => 'sometimes|boolean', 'media' => 'sometimes|array',
            'media.*.path' => 'required|string|max:500', 'media.*.alt' => 'nullable|string|max:200', 'media.*.sort_order' => 'sometimes|integer|min:0',
        ]);
        $saved = DB::transaction(function () use ($data, $product) {
            $variants = $data['variants']; $media = $data['media'] ?? null; unset($data['variants'], $data['media']);
            $product->fill($data); if (($data['status'] ?? null) === 'published' && ! $product->published_at) $product->published_at = now(); $product->save();
            $ids = [];
            foreach ($variants as $variant) { $id = $variant['id'] ?? null; $oldPrice = $id ? $product->variants()->whereKey($id)->value('price') : null; unset($variant['id']); $model = $product->variants()->updateOrCreate(['id' => $id], $variant); $ids[] = $model->id; if ($oldPrice === null || (int) $oldPrice !== (int) $model->price) DB::table('price_histories')->insert(['variant_id' => $model->id, 'price' => $model->price, 'recorded_at' => now()]); }
            $product->variants()->whereNotIn('id', $ids)->delete();
            if ($media !== null) { $product->media()->delete(); $product->media()->createMany(collect($media)->map(fn (array $item, int $index) => ['path' => $item['path'], 'alt' => $item['alt'] ?? $product->title, 'type' => 'image', 'sort_order' => $item['sort_order'] ?? $index])->all()); }
            return $product->load(['variants', 'media', 'category', 'brand']);
        });
        $this->alerts->process($saved);
        return $saved;
    }
}
