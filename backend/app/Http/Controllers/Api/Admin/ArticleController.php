<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function index(Request $request) { return DB::table('articles')->whereNull('deleted_at')->when($request->status, fn ($query, $status) => $query->where('status', $status))->latest()->paginate(20); }
    public function show(int $article) { return DB::table('articles')->whereNull('deleted_at')->find($article) ?? abort(404); }
    public function store(Request $request) { $data = $this->data($request); $data['author_id'] = $request->user()->id; $data['created_at'] = $data['updated_at'] = now(); $id = DB::table('articles')->insertGetId($data); return response()->json(DB::table('articles')->find($id), 201); }
    public function update(Request $request, int $article) { abort_unless(DB::table('articles')->whereNull('deleted_at')->where('id', $article)->exists(), 404); DB::table('articles')->where('id', $article)->update($this->data($request, $article) + ['updated_at' => now()]); return DB::table('articles')->find($article); }
    public function destroy(int $article) { DB::table('articles')->where('id', $article)->update(['deleted_at' => now(), 'updated_at' => now()]); return response()->noContent(); }

    private function data(Request $request, ?int $id = null): array
    {
        $data = $request->validate(['title' => 'required|string|max:200', 'slug' => ['required', 'alpha_dash', Rule::unique('articles')->ignore($id)], 'excerpt' => 'nullable|string', 'body' => 'required|string', 'cover' => 'nullable|string', 'status' => 'required|in:draft,published,archived', 'tags' => 'nullable|array', 'seo' => 'nullable|array', 'published_at' => 'nullable|date']);
        foreach (['tags', 'seo'] as $field) if (array_key_exists($field, $data)) $data[$field] = json_encode($data[$field], JSON_UNESCAPED_UNICODE);
        if ($data['status'] === 'published' && empty($data['published_at'])) $data['published_at'] = now();
        return $data;
    }
}
