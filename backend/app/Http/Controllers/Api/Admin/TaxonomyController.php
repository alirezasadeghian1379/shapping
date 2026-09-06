<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxonomyController extends Controller
{
    public function categories()
    {
        return Category::with('children')->orderBy('sort_order')->get();
    }

    public function storeCategory(Request $r)
    {
        return response()->json(Category::create($this->categoryData($r)), 201);
    }

    public function updateCategory(Request $r, Category $category)
    {
        $category->update($this->categoryData($r, $category));

        return $category;
    }

    public function deleteCategory(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }

    public function brands()
    {
        return Brand::latest()->paginate(50);
    }

    public function storeBrand(Request $r)
    {
        return response()->json(Brand::create($this->brandData($r)), 201);
    }

    public function updateBrand(Request $r, Brand $brand)
    {
        $brand->update($this->brandData($r, $brand));

        return $brand;
    }

    public function deleteBrand(Brand $brand)
    {
        $brand->delete();

        return response()->noContent();
    }

    private function categoryData(Request $r, ?Category $m = null): array
    {
        return $r->validate(['parent_id' => 'nullable|exists:categories,id', 'title' => 'required|string|max:150', 'slug' => ['required', 'alpha_dash', Rule::unique('categories')->ignore($m)], 'description' => 'nullable|string', 'image' => 'nullable|string', 'sort_order' => 'sometimes|integer|min:0', 'is_active' => 'sometimes|boolean', 'seo' => 'nullable|array']);
    }

    private function brandData(Request $r, ?Brand $m = null): array
    {
        return $r->validate(['title' => 'required|string|max:150', 'slug' => ['required', 'alpha_dash', Rule::unique('brands')->ignore($m)], 'logo' => 'nullable|string', 'description' => 'nullable|string', 'is_active' => 'sometimes|boolean', 'seo' => 'nullable|array']);
    }
}
