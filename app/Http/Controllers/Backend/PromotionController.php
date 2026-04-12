<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::query()->orderByDesc('id')->paginate(20);

        return view('backend.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('backend.promotions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Promotion::create([
            'name' => $request->input('name', 'Khuyến mãi mới'),
            'code' => $request->input('code') ?: null,
            'promotion_type' => $request->input('promotion_type', 'voucher'),
            'channel' => $request->input('channel', 'all'),
            'discount_type' => $request->input('discount_type', 'none'),
            'discount_value' => (float) $request->input('discount_value', 0),
            'min_order_value' => (float) $request->input('min_order_value', 0),
            'max_discount_value' => $request->filled('max_discount_value') ? (float) $request->input('max_discount_value') : null,
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'status' => $request->input('status', 'active'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('backend.promotions')->with('success', 'Đã tạo khuyến mãi.');
    }

    public function show(Promotion $promotion): View
    {
        $scopes = DB::table('promotion_scopes')->where('promotion_id', $promotion->id)->get();
        $usages = DB::table('coupon_usages')->where('promotion_id', $promotion->id)->orderByDesc('id')->limit(20)->get();

        return view('backend.promotions.show', compact('promotion', 'scopes', 'usages'));
    }

    public function edit(Promotion $promotion): View
    {
        return view('backend.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update([
            'name' => $request->input('name', $promotion->name),
            'code' => $request->input('code') ?: null,
            'promotion_type' => $request->input('promotion_type', $promotion->promotion_type),
            'channel' => $request->input('channel', $promotion->channel),
            'discount_type' => $request->input('discount_type', $promotion->discount_type),
            'discount_value' => (float) $request->input('discount_value', $promotion->discount_value),
            'min_order_value' => (float) $request->input('min_order_value', $promotion->min_order_value),
            'max_discount_value' => $request->filled('max_discount_value') ? (float) $request->input('max_discount_value') : null,
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'status' => $request->input('status', $promotion->status),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('backend.promotions')->with('success', 'Đã cập nhật khuyến mãi.');
    }

    public function destroy(Request $request, Promotion $promotion): RedirectResponse|JsonResponse
    {
        DB::table('promotion_scopes')->where('promotion_id', $promotion->id)->delete();
        DB::table('coupon_usages')->where('promotion_id', $promotion->id)->delete();
        DB::table('promo_tickers')->where('promotion_id', $promotion->id)->update(['promotion_id' => null]);
        DB::table('orders')->where('promotion_id', $promotion->id)->update(['promotion_id' => null]);
        DB::table('carts')->where('promotion_id', $promotion->id)->update(['promotion_id' => null]);
        $promotion->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khuyến mãi.',
                'promotion_id' => $promotion->id,
            ]);
        }

        return redirect()->route('backend.promotions')->with('success', 'Đã xóa khuyến mãi.');
    }
}
