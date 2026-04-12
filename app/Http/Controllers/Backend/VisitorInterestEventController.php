<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\VisitorInterestEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorInterestEventController extends Controller
{
    public function index(Request $request): View
    {
        $days = max(1, min(30, (int) $request->input('days', 7)));
        $cutoff = Carbon::now()->subDays($days);

        $topProducts = VisitorInterestEvent::query()
            ->selectRaw('
                COALESCE(product_slug, CONCAT("product-", product_id)) as interest_key,
                MAX(product_name) as product_name,
                MAX(product_slug) as product_slug,
                MAX(product_id) as product_id,
                SUM(CASE WHEN event_type = "product_view" THEN 1 ELSE 0 END) as views_count,
                SUM(CASE WHEN event_type = "add_to_cart" THEN qty ELSE 0 END) as add_to_cart_qty,
                COUNT(DISTINCT visitor_token) as unique_visitors,
                MAX(created_at) as last_interest_at
            ')
            ->where('created_at', '>=', $cutoff)
            ->whereNotNull('product_name')
            ->groupBy('interest_key')
            ->orderByDesc(DB::raw('(SUM(CASE WHEN event_type = "product_view" THEN 1 ELSE 0 END) + (SUM(CASE WHEN event_type = "add_to_cart" THEN qty ELSE 0 END) * 3))'))
            ->limit(20)
            ->get();

        $recentEvents = VisitorInterestEvent::query()
            ->where('created_at', '>=', $cutoff)
            ->orderByDesc('id')
            ->limit(80)
            ->get();

        $summary = [
            'views' => VisitorInterestEvent::query()->where('created_at', '>=', $cutoff)->where('event_type', 'product_view')->count(),
            'add_to_cart' => VisitorInterestEvent::query()->where('created_at', '>=', $cutoff)->where('event_type', 'add_to_cart')->sum('qty'),
            'unique_visitors' => VisitorInterestEvent::query()->where('created_at', '>=', $cutoff)->distinct('visitor_token')->count('visitor_token'),
        ];

        return view('backend.visitor-interest-events.index', compact('topProducts', 'recentEvents', 'summary', 'days'));
    }
}
