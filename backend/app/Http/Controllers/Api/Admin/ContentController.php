<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    private const RESOURCES = ['sliders', 'banners', 'faqs'];

    public function index(string $resource)
    {
        $this->ensureResource($resource);
        return DB::table($resource)->orderBy('sort_order')->get();
    }

    public function store(Request $request, string $resource)
    {
        $this->ensureResource($resource);
        $id = DB::table($resource)->insertGetId($this->data($request, $resource) + ['created_at' => now(), 'updated_at' => now()]);
        return response()->json(DB::table($resource)->find($id), 201);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $this->ensureResource($resource);
        abort_unless(DB::table($resource)->where('id', $id)->exists(), 404);
        DB::table($resource)->where('id', $id)->update($this->data($request, $resource) + ['updated_at' => now()]);
        return DB::table($resource)->find($id);
    }

    public function destroy(string $resource, int $id)
    {
        $this->ensureResource($resource);
        DB::table($resource)->where('id', $id)->delete();
        return response()->noContent();
    }

    private function ensureResource(string $resource): void { abort_unless(in_array($resource, self::RESOURCES, true), 404); }
    private function data(Request $request, string $resource): array
    {
        return match ($resource) {
            'sliders' => $request->validate(['title' => 'required|string|max:150', 'image' => 'required|string', 'mobile_image' => 'nullable|string', 'link' => 'nullable|string', 'position' => 'sometimes|string|max:50', 'sort_order' => 'sometimes|integer|min:0', 'is_active' => 'sometimes|boolean', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after:starts_at']),
            'banners' => $request->validate(['title' => 'required|string|max:150', 'image' => 'required|string', 'link' => 'nullable|string', 'placement' => 'required|string|max:50', 'sort_order' => 'sometimes|integer|min:0', 'is_active' => 'sometimes|boolean']),
            'faqs' => $request->validate(['question' => 'required|string|max:255', 'answer' => 'required|string', 'group' => 'sometimes|string|max:100', 'sort_order' => 'sometimes|integer|min:0', 'is_active' => 'sometimes|boolean']),
        };
    }
}
