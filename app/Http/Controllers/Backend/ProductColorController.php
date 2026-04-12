<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductColor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductColorController extends Controller
{
    public function index(): View
    {
        $productColors = ProductColor::query()->orderBy('sort_order')->orderByDesc('id')->get();

        return view('backend.product-colors.index', compact('productColors'));
    }

    public function create(): View
    {
        return view('backend.product-colors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ProductColor::create([
            'name' => $request->input('name', 'Màu mới'),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', 'mau-moi')),
            'hex_code' => $request->input('hex_code') ?: null,
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()->route('backend.product-colors')->with('success', 'Đã tạo màu sắc.');
    }

    public function show(ProductColor $productColor): View
    {
        return view('backend.product-colors.show', compact('productColor'));
    }

    public function edit(ProductColor $productColor): View
    {
        return view('backend.product-colors.edit', compact('productColor'));
    }

    public function update(Request $request, ProductColor $productColor): RedirectResponse
    {
        $productColor->update([
            'name' => $request->input('name', $productColor->name),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', $productColor->name)),
            'hex_code' => $request->input('hex_code') ?: null,
            'sort_order' => (int) $request->input('sort_order', $productColor->sort_order),
            'status' => $request->input('status', $productColor->status),
        ]);

        return redirect()->route('backend.product-colors')->with('success', 'Đã cập nhật màu sắc.');
    }

    public function destroy(Request $request, ProductColor $productColor): RedirectResponse|JsonResponse
    {
        $productColor->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa màu sắc.',
                'product_color_id' => $productColor->id,
            ]);
        }

        return redirect()->route('backend.product-colors')->with('success', 'Đã xóa màu sắc.');
    }
}
