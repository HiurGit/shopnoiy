<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PromoTicker;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoTickerController extends Controller
{
    public function index(): View
    {
        $promoTickers = PromoTicker::query()->orderByDesc('id')->get();

        return view('backend.promo-tickers.index', compact('promoTickers'));
    }

    public function create(): View
    {
        $promotions = Promotion::query()->orderByDesc('id')->get();

        return view('backend.promo-tickers.create', compact('promotions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $name = trim((string) $request->input('name', ''));
        $contentText = trim((string) $request->input('content_text', ''));

        PromoTicker::query()->create([
            'promotion_id' => $request->filled('promotion_id') ? (int) $request->input('promotion_id') : null,
            'name' => $name !== '' ? $name : 'Ticker dau trang',
            'content_text' => $contentText,
            'background_style' => $request->input('background_style'),
            'text_color' => $request->input('text_color'),
            'speed_seconds' => (int) $request->input('speed_seconds', 18),
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()->route('backend.promo-tickers')->with('success', 'Da tao promo ticker.');
    }

    public function show(PromoTicker $promoTicker): View
    {
        return view('backend.promo-tickers.show', compact('promoTicker'));
    }

    public function edit(PromoTicker $promoTicker): View
    {
        $promotions = Promotion::query()->orderByDesc('id')->get();

        return view('backend.promo-tickers.edit', compact('promoTicker', 'promotions'));
    }

    public function update(Request $request, PromoTicker $promoTicker): RedirectResponse
    {
        $name = trim((string) $request->input('name', ''));
        $contentText = trim((string) $request->input('content_text', $promoTicker->content_text));

        $promoTicker->update([
            'promotion_id' => $request->filled('promotion_id') ? (int) $request->input('promotion_id') : null,
            'name' => $name !== '' ? $name : ($promoTicker->name ?: 'Ticker dau trang'),
            'content_text' => $contentText,
            'background_style' => $request->input('background_style'),
            'text_color' => $request->input('text_color'),
            'speed_seconds' => (int) $request->input('speed_seconds', $promoTicker->speed_seconds),
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'status' => $request->input('status', $promoTicker->status),
        ]);

        return redirect()->route('backend.promo-tickers')->with('success', 'Da cap nhat promo ticker.');
    }

    public function destroy(Request $request, PromoTicker $promoTicker): RedirectResponse|JsonResponse
    {
        $promoTicker->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Da xoa promo ticker.',
                'promo_ticker_id' => $promoTicker->id,
            ]);
        }

        return redirect()->route('backend.promo-tickers')->with('success', 'Da xoa promo ticker.');
    }
}
