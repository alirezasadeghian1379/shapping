<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SeoController extends Controller
{
    public function sitemap()
    {
        return [
            'products' => DB::table('products')->where('status', 'published')->whereNull('deleted_at')->get(['slug', 'updated_at']),
            'categories' => DB::table('categories')->where('is_active', true)->whereNull('deleted_at')->get(['slug', 'updated_at']),
            'articles' => DB::table('articles')->where('status', 'published')->whereNull('deleted_at')->get(['slug', 'updated_at']),
        ];
    }
}
