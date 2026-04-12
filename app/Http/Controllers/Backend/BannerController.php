<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HomeSectionItem;
use App\Support\UploadPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = HomeSectionItem::query()
            ->where('item_type', 'banner')
            ->orderBy('section_id')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $positions = DB::table('home_sections')->pluck('title', 'id');

        return view('backend.banners.index', compact('banners', 'positions'));
    }

    public function create(): View
    {
        $positions = DB::table('home_sections')->orderBy('sort_order')->orderBy('title')->get();

        return view('backend.banners.create', compact('positions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $imageUrl = trim((string) $request->input('image_url', ''));

        if ($request->hasFile('image')) {
            $imageUrl = $this->storeUploadedImage($request->file('image'));
        }

        HomeSectionItem::query()->create([
            'section_id' => (int) $request->input('section_id', 0),
            'item_type' => 'banner',
            'ref_id' => $request->filled('ref_id') ? (int) $request->input('ref_id') : null,
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'target_url' => $request->input('target_url'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'meta_json' => $request->filled('meta_json') ? $request->input('meta_json') : null,
        ]);

        return redirect()->route('backend.banners')->with('success', 'Da tao banner.');
    }

    public function show(HomeSectionItem $banner): View
    {
        $this->ensureBanner($banner);
        $position = DB::table('home_sections')->where('id', $banner->section_id)->first();

        return view('backend.banners.show', compact('banner', 'position'));
    }

    public function edit(HomeSectionItem $banner): View
    {
        $this->ensureBanner($banner);
        $positions = DB::table('home_sections')->orderBy('sort_order')->orderBy('title')->get();

        return view('backend.banners.edit', compact('banner', 'positions'));
    }

    public function update(Request $request, HomeSectionItem $banner): RedirectResponse
    {
        $this->ensureBanner($banner);

        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $imageUrl = trim((string) $request->input('image_url', (string) $banner->image_url));

        if ($request->hasFile('image')) {
            $newImage = $this->storeUploadedImage($request->file('image'));
            $this->deleteBannerImage($banner->image_url);
            $imageUrl = $newImage;
        }

        $banner->update([
            'section_id' => (int) $request->input('section_id', $banner->section_id),
            'item_type' => 'banner',
            'ref_id' => $request->filled('ref_id') ? (int) $request->input('ref_id') : null,
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'target_url' => $request->input('target_url'),
            'sort_order' => (int) $request->input('sort_order', $banner->sort_order),
            'is_active' => $request->boolean('is_active'),
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'meta_json' => $request->filled('meta_json') ? $request->input('meta_json') : null,
        ]);

        return redirect()->route('backend.banners')->with('success', 'Da cap nhat banner.');
    }

    public function destroy(Request $request, HomeSectionItem $banner): RedirectResponse|JsonResponse
    {
        $this->ensureBanner($banner);
        $this->deleteBannerImage($banner->image_url);
        $banner->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Da xoa banner.',
                'banner_id' => $banner->id,
            ]);
        }

        return redirect()->route('backend.banners')->with('success', 'Da xoa banner.');
    }

    private function ensureBanner(HomeSectionItem $item): void
    {
        abort_unless($item->item_type === 'banner', 404);
    }

    private function storeUploadedImage(UploadedFile $file): string
    {
        $directory = UploadPath::absolute('banners');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = now()->format('YmdHis') . '-' . Str::random(8) . '.' . strtolower($file->extension() ?: 'jpg');
        $file->move($directory, $filename);

        return '/' . trim(UploadPath::relative('banners') . '/' . $filename, '/');
    }

    private function deleteBannerImage(?string $imageUrl): void
    {
        if (empty($imageUrl) || str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return;
        }

        $normalizedPath = '/' . ltrim($imageUrl, '/');
        $bannerPrefix = '/' . trim(UploadPath::relative('banners'), '/') . '/';
        if (!str_starts_with($normalizedPath, $bannerPrefix)) {
            return;
        }

        $fullPath = public_path(ltrim($normalizedPath, '/'));
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
