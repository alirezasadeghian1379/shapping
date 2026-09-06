<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscoveryAnalyticsController extends Controller
{
    public function __invoke(Request $request)
    {
        $days = min(max($request->integer('days', 30), 7), 90);
        $from = now()->subDays($days);

        return [
            'summary' => [
                'searches' => DB::table('search_logs')->where('created_at', '>=', $from)->count(),
                'zero_results' => DB::table('search_logs')->where('created_at', '>=', $from)->where('result_count', 0)->count(),
                'active_alerts' => DB::table('product_alerts')->whereNull('notified_at')->count(),
                'sent_alerts' => DB::table('product_alerts')->whereNotNull('notified_at')->count(),
            ],
            'top_searches' => DB::table('search_logs')->where('created_at', '>=', $from)
                ->select('query', DB::raw('COUNT(*) as searches'), DB::raw('MAX(result_count) as result_count'))
                ->groupBy('query')->orderByDesc('searches')->limit(15)->get(),
            'zero_result_searches' => DB::table('search_logs')->where('created_at', '>=', $from)->where('result_count', 0)
                ->select('query', DB::raw('COUNT(*) as searches'))->groupBy('query')->orderByDesc('searches')->limit(15)->get(),
        ];
    }
}
