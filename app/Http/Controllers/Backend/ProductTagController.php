<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductTagController extends Controller
{
    public function index(): View
    {
        $productTags = ProductTag::query()->orderBy('sort_order')->orderByDesc('id')->get();

        return view('backend.product-tags.index', compact('productTags'));
    }

    public function create(): View
    {
        return view('backend.product-tags.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ProductTag::create([
            'name' => $request->input('name', 'Tag mới'),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', 'tag-moi')),
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()->route('backend.product-tags')->with('success', 'Đã tạo tag sản phẩm.');
    }

    public function show(ProductTag $productTag): View
    {
        return view('backend.product-tags.show', compact('productTag'));
    }

    public function edit(ProductTag $productTag): View
    {
        return view('backend.product-tags.edit', compact('productTag'));
    }

    public function update(Request $request, ProductTag $productTag): RedirectResponse
    {
        $productTag->update([
            'name' => $request->input('name', $productTag->name),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', $productTag->name)),
            'sort_order' => (int) $request->input('sort_order', $productTag->sort_order),
            'status' => $request->input('status', $productTag->status),
        ]);

        return redirect()->route('backend.product-tags')->with('success', 'Đã cập nhật tag sản phẩm.');
    }

    public function destroy(Request $request, ProductTag $productTag): RedirectResponse|JsonResponse
    {
        $productTag->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tag sản phẩm.',
                'product_tag_id' => $productTag->id,
            ]);
        }

        return redirect()->route('backend.product-tags')->with('success', 'Đã xóa tag sản phẩm.');
    }
}
