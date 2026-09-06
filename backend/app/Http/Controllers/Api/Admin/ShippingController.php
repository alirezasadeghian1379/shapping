<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ShippingController extends Controller
{
    public function methods() { return DB::table('shipping_methods')->orderByDesc('is_active')->get(); }
    public function storeMethod(Request $request) { $id = DB::table('shipping_methods')->insertGetId($this->methodData($request) + $this->timestamps()); return response()->json(DB::table('shipping_methods')->find($id), 201); }
    public function updateMethod(Request $request, int $method) { DB::table('shipping_methods')->where('id', $method)->update($this->methodData($request, $method) + ['updated_at' => now()]); return DB::table('shipping_methods')->find($method); }
    public function deleteMethod(int $method) { abort_if(DB::table('orders')->where('shipping_method_id', $method)->exists(), 422, 'روش استفاده‌شده را غیرفعال کنید.'); DB::table('shipping_methods')->where('id', $method)->delete(); return response()->noContent(); }
    public function slots(Request $request) { return DB::table('delivery_slots')->when($request->from, fn ($query, $from) => $query->whereDate('date', '>=', $from))->orderBy('date')->orderBy('starts_at')->paginate(50); }
    public function storeSlot(Request $request) { $id = DB::table('delivery_slots')->insertGetId($this->slotData($request) + $this->timestamps()); return response()->json(DB::table('delivery_slots')->find($id), 201); }
    public function updateSlot(Request $request, int $slot) { DB::table('delivery_slots')->where('id', $slot)->update($this->slotData($request) + ['updated_at' => now()]); return DB::table('delivery_slots')->find($slot); }
    public function deleteSlot(int $slot) { abort_if(DB::table('delivery_slots')->where('id', $slot)->value('reserved') > 0, 422, 'بازه دارای رزرو قابل حذف نیست.'); DB::table('delivery_slots')->where('id', $slot)->delete(); return response()->noContent(); }

    private function methodData(Request $request, ?int $id = null): array
    {
        return $request->validate(['title' => 'required|string|max:150', 'code' => ['required', 'alpha_dash', Rule::unique('shipping_methods')->ignore($id)], 'description' => 'nullable|string', 'base_cost' => 'required|integer|min:0', 'free_threshold' => 'nullable|integer|min:0', 'rules' => 'nullable|array', 'is_active' => 'sometimes|boolean']);
    }

    private function slotData(Request $request): array
    {
        return $request->validate(['date' => 'required|date', 'starts_at' => 'required|date_format:H:i', 'ends_at' => 'required|date_format:H:i|after:starts_at', 'capacity' => 'required|integer|min:1', 'extra_cost' => 'sometimes|integer|min:0', 'is_active' => 'sometimes|boolean']);
    }

    private function timestamps(): array { return ['created_at' => now(), 'updated_at' => now()]; }
}
