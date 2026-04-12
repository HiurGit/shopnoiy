<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HomeSectionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeSectionItemController extends Controller
{
    public function index(): View
    {
        $this->ensureManagedSections();

        $items = HomeSectionItem::query()->orderByDesc('id')->get();
        $sections = DB::table('home_sections')->pluck('title', 'id');
        $sectionStates = DB::table('home_sections')
            ->select('id', 'section_key', 'title', 'section_type', 'sort_order', 'is_active')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('backend.home-management.index', compact('items', 'sections', 'sectionStates'));
    }

    public function create(): View
    {
        $this->ensureManagedSections();
        $sections = DB::table('home_sections')->orderBy('sort_order')->get();

        return view('backend.home-management.create', compact('sections'));
    }

    public function store(Request $request): RedirectResponse
    {
        HomeSectionItem::create([
            'section_id' => (int) $request->input('section_id', 0),
            'item_type' => $request->input('item_type', 'banner'),
            'ref_id' => $request->filled('ref_id') ? (int) $request->input('ref_id') : null,
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'image_url' => $request->input('image_url'),
            'target_url' => $request->input('target_url'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'meta_json' => $request->filled('meta_json') ? $request->input('meta_json') : null,
        ]);

        return redirect()->route('backend.home-management')->with('success', 'Đã tạo mục trang chủ.');
    }

    public function show(HomeSectionItem $homeItem): View
    {
        $this->ensureManagedSections();
        $section = DB::table('home_sections')->where('id', $homeItem->section_id)->first();

        return view('backend.home-management.show', compact('homeItem', 'section'));
    }

    public function edit(HomeSectionItem $homeItem): View
    {
        $this->ensureManagedSections();
        $sections = DB::table('home_sections')->orderBy('sort_order')->get();

        return view('backend.home-management.edit', compact('homeItem', 'sections'));
    }

    public function update(Request $request, HomeSectionItem $homeItem): RedirectResponse
    {
        $homeItem->update([
            'section_id' => (int) $request->input('section_id', $homeItem->section_id),
            'item_type' => $request->input('item_type', $homeItem->item_type),
            'ref_id' => $request->filled('ref_id') ? (int) $request->input('ref_id') : null,
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'image_url' => $request->input('image_url'),
            'target_url' => $request->input('target_url'),
            'sort_order' => (int) $request->input('sort_order', $homeItem->sort_order),
            'is_active' => $request->boolean('is_active'),
            'start_at' => $request->input('start_at') ?: null,
            'end_at' => $request->input('end_at') ?: null,
            'meta_json' => $request->filled('meta_json') ? $request->input('meta_json') : null,
        ]);

        return redirect()->route('backend.home-management')->with('success', 'Đã cập nhật mục trang chủ.');
    }

    public function destroy(Request $request, HomeSectionItem $homeItem): RedirectResponse|JsonResponse
    {
        $homeItem->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mục trang chủ.',
                'home_item_id' => $homeItem->id,
            ]);
        }

        return redirect()->route('backend.home-management')->with('success', 'Đã xóa mục trang chủ.');
    }
    public function updateSectionVisibility(Request $request, int $section): RedirectResponse|JsonResponse
    {
        $this->ensureManagedSections();

        $sectionRow = DB::table('home_sections')->where('id', $section)->first();
        abort_unless($sectionRow, 404);

        $isActive = $request->boolean('is_active');

        DB::table('home_sections')->where('id', $section)->update([
            'is_active' => $isActive,
            'updated_by' => 'admin',
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'section_id' => $section,
                'is_active' => $isActive,
            ]);
        }

        return redirect()
            ->route('backend.home-management')
            ->with('success', 'ÄÃ£ cáº­p nháº­t hiá»ƒn thá»‹ section ' . ($sectionRow->title ?? ('#' . $section)) . '.');
    }

    private function ensureManagedSections(): void
    {
        $now = now();
        $defaults = [
            ['section_key' => 'hero', 'title' => 'Hero Banner', 'section_type' => 'hero', 'sort_order' => 1],
            ['section_key' => 'featured-products', 'title' => 'San pham noi bat', 'section_type' => 'featured_products', 'sort_order' => 2],
            ['section_key' => 'contact', 'title' => 'Lien he', 'section_type' => 'contact', 'sort_order' => 3],
        ];

        foreach ($defaults as $default) {
            $existing = DB::table('home_sections')->where('section_key', $default['section_key'])->first();

            DB::table('home_sections')->updateOrInsert(
                ['section_key' => $default['section_key']],
                [
                    'title' => $default['title'],
                    'section_type' => $default['section_type'],
                    'sort_order' => $default['sort_order'],
                    'is_active' => $existing->is_active ?? 1,
                    'config_json' => $existing->config_json ?? null,
                    'updated_by' => 'admin',
                    'updated_at' => $now,
                    'created_at' => $existing->created_at ?? $now,
                ]
            );
        }
    }
}
