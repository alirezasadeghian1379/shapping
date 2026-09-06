<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        return DB::table('comments')->join('users', 'users.id', '=', 'comments.user_id')->select('comments.*', 'users.name as user_name', 'users.mobile')->when($request->status, fn ($query, $status) => $query->where('comments.status', $status))->when($request->type, fn ($query, $type) => $query->where('commentable_type', $type))->orderByDesc('comments.id')->paginate(30);
    }

    public function moderate(Request $request, int $comment)
    {
        $data = $request->validate(['status' => 'required|in:pending,approved,rejected']);
        abort_unless(DB::table('comments')->where('id', $comment)->update($data + ['updated_at' => now()]), 404);
        return DB::table('comments')->find($comment);
    }

    public function destroy(int $comment) { DB::table('comments')->where('id', $comment)->update(['deleted_at' => now(), 'updated_at' => now()]); return response()->noContent(); }

    public function reply(Request $request, int $comment)
    {
        $parent = \App\Models\Comment::findOrFail($comment);
        $data = $request->validate(['body' => 'required|string|min:2|max:2000']);
        return response()->json($parent->commentable->comments()->create(['user_id' => $request->user()->id, 'parent_id' => $parent->id, 'body' => $data['body'], 'status' => 'approved', 'is_staff' => true]), 201);
    }
}
