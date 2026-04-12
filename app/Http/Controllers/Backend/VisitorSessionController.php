<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\VisitorSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitorSessionController extends Controller
{
    public function index(Request $request): View
    {
        $activeWithinMinutes = 15;
        $query = trim((string) $request->input('q', ''));
        $cutoff = Carbon::now()->subMinutes($activeWithinMinutes);

        $visitorsQuery = VisitorSession::query()
            ->where('last_seen_at', '>=', $cutoff);

        if ($query !== '') {
            $visitorsQuery->where(function ($builder) use ($query): void {
                $builder
                    ->where('page_title', 'like', '%' . $query . '%')
                    ->orWhere('current_url', 'like', '%' . $query . '%')
                    ->orWhere('activity_label', 'like', '%' . $query . '%')
                    ->orWhere('visitor_token', 'like', '%' . $query . '%');
            });
        }

        $visitors = $visitorsQuery
            ->orderByDesc('last_seen_at')
            ->get();

        $now = Carbon::now();
        $summary = [
            'online_now' => $visitors->filter(fn (VisitorSession $visitor) => $visitor->last_seen_at?->greaterThanOrEqualTo($now->copy()->subMinutes(2)))->count(),
            'active_15m' => $visitors->count(),
            'in_checkout' => $visitors->where('page_type', 'checkout')->count(),
            'with_cart' => $visitors->filter(fn (VisitorSession $visitor) => (int) $visitor->cart_count > 0)->count(),
        ];

        return view('backend.visitor-sessions.index', compact('visitors', 'summary', 'query', 'activeWithinMinutes'));
    }
}
