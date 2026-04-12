<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductTargetController extends Controller
{
    public function index(): View
    {
        $productTargets = ProductTarget::query()->orderBy('sort_order')->orderByDesc('id')->get();

        return view('backend.product-targets.index', compact('productTargets'));
    }

    public function create(): View
    {
        return view('backend.product-targets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ProductTarget::create([
            'name' => $request->input('name', 'Đối tượng mới'),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', 'doi-tuong-moi')),
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()->route('backend.product-targets')->with('success', 'Đã tạo đối tượng danh mục.');
    }

    public function show(ProductTarget $productTarget): View
    {
        return view('backend.product-targets.show', compact('productTarget'));
    }

    public function edit(ProductTarget $productTarget): View
    {
        return view('backend.product-targets.edit', compact('productTarget'));
    }

    public function update(Request $request, ProductTarget $productTarget): RedirectResponse
    {
        $productTarget->update([
            'name' => $request->input('name', $productTarget->name),
            'slug' => $request->input('slug') ?: str()->slug((string) $request->input('name', $productTarget->name)),
            'sort_order' => (int) $request->input('sort_order', $productTarget->sort_order),
            'status' => $request->input('status', $productTarget->status),
        ]);

        return redirect()->route('backend.product-targets')->with('success', 'Đã cập nhật đối tượng danh mục.');
    }

    public function destroy(Request $request, ProductTarget $productTarget): RedirectResponse|JsonResponse
    {
        $hasCategories = Category::query()->where('product_target_id', $productTarget->id)->exists();
        if ($hasCategories) {
            $message = 'Không thể xóa đối tượng đang được gắn cho danh mục.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('backend.product-targets')->with('error', $message);
        }

        $productTarget->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa đối tượng sản phẩm.',
                'product_target_id' => $productTarget->id,
            ]);
        }

        return redirect()->route('backend.product-targets')->with('success', 'Đã xóa đối tượng danh mục.');
    }
}
