<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductSizeController extends Controller
{
    public function index(): View
    {
        $productSizes = ProductSize::query()->orderBy('sort_order')->orderByDesc('id')->get();

        return view('backend.product-sizes.index', compact('productSizes'));
    }

    public function create(): View
    {
        return view('backend.product-sizes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ProductSize::create([
            'name' => $request->input('name', 'Size mới'),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', 'size-moi')),
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()->route('backend.product-sizes')->with('success', 'Đã tạo kích thước.');
    }

    public function show(ProductSize $productSize): View
    {
        return view('backend.product-sizes.show', compact('productSize'));
    }

    public function edit(ProductSize $productSize): View
    {
        return view('backend.product-sizes.edit', compact('productSize'));
    }

    public function update(Request $request, ProductSize $productSize): RedirectResponse
    {
        $productSize->update([
            'name' => $request->input('name', $productSize->name),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', $productSize->name)),
            'sort_order' => (int) $request->input('sort_order', $productSize->sort_order),
            'status' => $request->input('status', $productSize->status),
        ]);

        return redirect()->route('backend.product-sizes')->with('success', 'Đã cập nhật kích thước.');
    }

    public function destroy(Request $request, ProductSize $productSize): RedirectResponse|JsonResponse
    {
        $productSize->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa kích thước.',
                'product_size_id' => $productSize->id,
            ]);
        }

        return redirect()->route('backend.product-sizes')->with('success', 'Đã xóa kích thước.');
    }
}
