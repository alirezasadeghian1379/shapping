<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{
    public function index(Request $request) { return DiscountCode::latest()->paginate(min($request->integer('per_page', 20), 100)); }
    public function store(Request $request) { return response()->json(DiscountCode::create($this->data($request)), 201); }
    public function show(DiscountCode $discount) { return $discount; }
    public function update(Request $request, DiscountCode $discount) { $discount->update($this->data($request, $discount)); return $discount; }
    public function destroy(DiscountCode $discount) { abort_if($discount->used_count > 0, 422, 'کد استفاده‌شده را غیرفعال کنید؛ حذف آن مجاز نیست.'); $discount->delete(); return response()->noContent(); }

    private function data(Request $request, ?DiscountCode $discount = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('discount_codes')->ignore($discount)],
            'title' => 'required|string|max:150', 'type' => 'required|in:percent,fixed', 'value' => 'required|integer|min:1',
            'max_discount' => 'nullable|required_if:type,percent|integer|min:1', 'min_order_amount' => 'sometimes|integer|min:0',
            'usage_limit' => 'nullable|integer|min:1', 'per_user_limit' => 'sometimes|integer|min:1', 'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at', 'is_active' => 'sometimes|boolean', 'conditions' => 'nullable|array',
        ]);
    }
}
