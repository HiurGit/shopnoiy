<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VisitorSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitorTrackingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visitor_token' => ['required', 'string', 'min:12', 'max:80'],
            'route_name' => ['nullable', 'string', 'max:190'],
            'page_type' => ['nullable', 'string', 'max:80'],
            'activity_label' => ['nullable', 'string', 'max:190'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'current_path' => ['nullable', 'string', 'max:255'],
            'current_url' => ['nullable', 'url', 'max:2000'],
            'referrer_url' => ['nullable', 'url', 'max:2000'],
            'cart_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'cart_value' => ['nullable', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'],
            'meta.product_slug' => ['nullable', 'string', 'max:190'],
            'meta.product_name' => ['nullable', 'string', 'max:255'],
            'meta.search_query' => ['nullable', 'string', 'max:255'],
            'meta.customer_name' => ['nullable', 'string', 'max:150'],
            'meta.customer_phone' => ['nullable', 'string', 'max:20'],
            'meta.customer_email' => ['nullable', 'string', 'max:190'],
        ]);

        $now = Carbon::now();
        $visitor = VisitorSession::query()
            ->where('visitor_token', $validated['visitor_token'])
            ->first();

        $payload = [
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'route_name' => $validated['route_name'] ?? null,
            'page_type' => $validated['page_type'] ?? null,
            'activity_label' => $validated['activity_label'] ?? null,
            'page_title' => $validated['page_title'] ?? null,
            'current_path' => $validated['current_path'] ?? null,
            'current_url' => $validated['current_url'] ?? null,
            'referrer_url' => $validated['referrer_url'] ?? null,
            'cart_count' => (int) ($validated['cart_count'] ?? 0),
            'cart_value' => (int) ($validated['cart_value'] ?? 0),
            'meta_json' => $validated['meta'] ?? null,
            'user_agent' => substr((string) $request->userAgent(), 0, 65535),
            'last_seen_at' => $now,
            'updated_at' => $now,
        ];

        if ($visitor) {
            $visitor->fill($payload)->save();
        } else {
            VisitorSession::query()->create(array_merge($payload, [
                'visitor_token' => $validated['visitor_token'],
                'first_seen_at' => $now,
                'created_at' => $now,
            ]));
        }

        return response()->json([
            'success' => true,
            'tracked_at' => $now->toIso8601String(),
        ]);
    }
}
