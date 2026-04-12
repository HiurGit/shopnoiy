<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductTarget;
use App\Support\UploadPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    private const MAX_CATEGORY_DEPTH = 3;

    public function index(): View
    {
        $categoryRows = $this->buildCategoryTreeRows();
        $parentCategories = $categoryRows->where('level', 1)->values();
        $childCategories = $categoryRows->where('level', 2)->values();
        $grandchildCategories = $categoryRows->where('level', 3)->values();

        return view('backend.categories.index', compact('parentCategories', 'childCategories', 'grandchildCategories'));
    }

    public function create(Request $request): View
    {
        $type = $request->string('type')->value() ?: 'parent';
        if (!in_array($type, ['parent', 'child', 'grandchild'], true)) {
            $type = 'parent';
        }

        $parentOptions = $this->buildParentOptions();
        $productTargets = ProductTarget::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $defaultParentId = null;

        if ($type === 'child') {
            $defaultParentId = $parentOptions->firstWhere('level', 1)['id'] ?? null;
        }

        if ($type === 'grandchild') {
            $defaultParentId = $parentOptions->firstWhere('level', 2)['id'] ?? null;
        }

        return view('backend.categories.create', compact('parentOptions', 'productTargets', 'type', 'defaultParentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'product_target_id' => ['nullable', 'integer', 'exists:product_targets,id'],
        ]);

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $name = 'Danh mục mới';
        }

        $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;

        if (!is_null($parentId) && !Category::query()->whereKey($parentId)->exists()) {
            return back()->withInput()->with('error', 'Danh mục cha không tồn tại.');
        }

        $level = $this->resolveLevelByParentId($parentId);
        if ($level > self::MAX_CATEGORY_DEPTH) {
            return back()->withInput()->with('error', 'Chỉ hỗ trợ tối đa 3 cấp danh mục (cha, con, cháu).');
        }

        $data = [
            'name' => $name,
            'parent_id' => $parentId,
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => in_array($request->input('status'), ['active', 'inactive'], true) ? $request->input('status') : 'active',
            'description' => $request->input('description'),
            'icon_class' => $request->input('icon_class'),
            'product_target_id' => $request->filled('product_target_id') ? (int) $request->input('product_target_id') : null,
        ];

        $data['slug'] = $this->ensureUniqueSlug((string) $request->input('slug', ''), $data['name']);

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeUploadedImage($request->file('image'));
        }

        Category::create($data);

        return redirect()->route('backend.categories')->with('success', 'Đã tạo danh mục thành công.');
    }

    public function show(Category $category): View
    {
        $category->load(['parent:id,name', 'target:id,name']);

        $children = Category::query()
            ->where('parent_id', $category->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $productCount = DB::table('products')->where('category_id', $category->id)->count();

        $treeRows = $this->buildCategoryTreeRows();
        $currentRow = $treeRows->firstWhere('id', $category->id);
        $currentLevel = $currentRow['level'] ?? 1;
        $currentPath = $currentRow['path'] ?? $category->name;
        $levelLabel = $this->levelLabel($currentLevel);

        return view('backend.categories.show', compact('category', 'children', 'productCount', 'currentLevel', 'currentPath', 'levelLabel'));
    }

    public function edit(Category $category): View
    {
        $excludedIds = array_merge([$category->id], $this->descendantIds($category->id));
        $parentOptions = $this->buildParentOptions($excludedIds);
        $productTargets = ProductTarget::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $currentLevel = $this->resolveLevelByParentId($category->parent_id);
        $type = $currentLevel === 1 ? 'parent' : ($currentLevel === 2 ? 'child' : 'grandchild');

        return view('backend.categories.edit', compact('category', 'parentOptions', 'productTargets', 'type'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'product_target_id' => ['nullable', 'integer', 'exists:product_targets,id'],
        ]);

        $name = trim((string) $request->input('name', $category->name));
        if ($name === '') {
            $name = $category->name;
        }

        $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;

        if (!is_null($parentId) && !Category::query()->whereKey($parentId)->exists()) {
            return back()->withInput()->with('error', 'Danh mục cha không tồn tại.');
        }

        if (!is_null($parentId) && $parentId === (int) $category->id) {
            return back()->withInput()->with('error', 'Danh mục không thể là cha của chính nó.');
        }

        if (!is_null($parentId) && in_array($parentId, $this->descendantIds($category->id), true)) {
            return back()->withInput()->with('error', 'Không thể chọn danh mục con/cháu làm danh mục cha.');
        }

        $newLevel = $this->resolveLevelByParentId($parentId);
        if ($newLevel > self::MAX_CATEGORY_DEPTH) {
            return back()->withInput()->with('error', 'Chỉ hỗ trợ tối đa 3 cấp danh mục (cha, con, cháu).');
        }

        $subtreeHeight = $this->subtreeHeight($category->id);
        if (($newLevel + $subtreeHeight - 1) > self::MAX_CATEGORY_DEPTH) {
            return back()->withInput()->with('error', 'Cập nhật này sẽ làm cây danh mục vượt quá 3 cấp.');
        }

        $data = [
            'name' => $name,
            'parent_id' => $parentId,
            'sort_order' => (int) $request->input('sort_order', $category->sort_order),
            'status' => in_array($request->input('status'), ['active', 'inactive'], true) ? $request->input('status') : $category->status,
            'description' => $request->input('description'),
            'icon_class' => $request->input('icon_class'),
            'product_target_id' => $request->filled('product_target_id') ? (int) $request->input('product_target_id') : null,
        ];

        $data['slug'] = $this->ensureUniqueSlug((string) $request->input('slug', ''), $data['name'], $category->id);

        if ($request->hasFile('image')) {
            $newImage = $this->storeUploadedImage($request->file('image'));
            $this->deletePublicFile($category->image_url);
            $data['image_url'] = $newImage;
        }

        $category->update($data);

        return redirect()->route('backend.categories')->with('success', 'Đã cập nhật danh mục thành công.');
    }

    public function quickUpdateSort(Request $request, Category $category): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $category->update([
            'sort_order' => (int) $validated['sort_order'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật số thứ tự danh mục.',
                'category_id' => $category->id,
                'sort_order' => (int) $category->sort_order,
            ]);
        }

        return redirect()->route('backend.categories')->with('success', 'Đã cập nhật số thứ tự danh mục.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse|JsonResponse
    {
        $hasChildren = Category::query()->where('parent_id', $category->id)->exists();
        if ($hasChildren) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục cha còn danh mục con/cháu.',
                ], 422);
            }

            return back()->with('error', 'Không thể xóa danh mục cha còn danh mục con/cháu.');
        }

        $hasProducts = DB::table('products')->where('category_id', $category->id)->exists();
        if ($hasProducts) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục đang có sản phẩm.',
                ], 422);
            }

            return back()->with('error', 'Không thể xóa danh mục đang có sản phẩm.');
        }

        $category->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa danh mục thành công.',
                'category_id' => $category->id,
            ]);
        }

        return redirect()->route('backend.categories')->with('success', 'Đã xóa danh mục thành công.');
    }


    private function ensureUniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        $base = $base !== '' ? $base : 'danh-muc';
        $candidate = $base;
        $counter = 1;

        while (
            Category::query()
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function storeUploadedImage(UploadedFile $file): string
    {
        $directory = UploadPath::absolute('categories');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = now()->format('YmdHis') . '-' . Str::random(8) . '.' . strtolower($file->extension() ?: 'jpg');
        $file->move($directory, $filename);

        return UploadPath::relative('categories') . '/' . $filename;
    }

    private function deletePublicFile(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $fullPath = public_path($relativePath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function buildParentOptions(array $excludedIds = []): Collection
    {
        return $this->buildCategoryTreeRows()
            ->filter(fn (array $row) => $row['level'] <= 2 && !in_array($row['id'], $excludedIds, true))
            ->values();
    }

    private function buildCategoryTreeRows(): Collection
    {
        $categories = Category::query()
            ->with('target:id,name')
            ->select('id', 'parent_id', 'name', 'slug', 'sort_order', 'status', 'updated_at', 'product_target_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        $childrenByParent = $categories->groupBy(fn (Category $category) => $category->parent_id ?? 0);
        $rows = collect();

        $walk = function (?int $parentId, int $level, string $prefix) use (&$walk, $childrenByParent, $rows): void {
            $key = $parentId ?? 0;
            /** @var Collection<int, Category> $children */
            $children = $childrenByParent->get($key, collect());

            foreach ($children as $child) {
                $path = $prefix === '' ? $child->name : ($prefix . ' > ' . $child->name);

                $rows->push([
                    'id' => (int) $child->id,
                    'parent_id' => $child->parent_id ? (int) $child->parent_id : null,
                    'level' => $level,
                    'level_label' => $this->levelLabel($level),
                    'path' => $path,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'target_name' => $child->target?->name,
                    'sort_order' => $child->sort_order,
                    'status' => $child->status,
                    'updated_at' => $child->updated_at,
                ]);

                if ($level < self::MAX_CATEGORY_DEPTH) {
                    $walk((int) $child->id, $level + 1, $path);
                }
            }
        };

        $walk(null, 1, '');

        return $rows;
    }

    private function resolveLevelByParentId(?int $parentId): int
    {
        if (is_null($parentId)) {
            return 1;
        }

        $level = 1;
        $cursor = $parentId;
        $guard = 0;

        while (!is_null($cursor)) {
            $guard++;
            if ($guard > 20) {
                return self::MAX_CATEGORY_DEPTH + 1;
            }

            $level++;
            if ($level > self::MAX_CATEGORY_DEPTH) {
                return $level;
            }

            $cursor = Category::query()->whereKey($cursor)->value('parent_id');
            $cursor = is_null($cursor) ? null : (int) $cursor;
        }

        return $level;
    }

    private function descendantIds(int $categoryId): array
    {
        $descendantIds = [];
        $queue = [$categoryId];

        while (!empty($queue)) {
            $children = Category::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($children)) {
                break;
            }

            $descendantIds = array_merge($descendantIds, $children);
            $queue = $children;
        }

        return array_values(array_unique($descendantIds));
    }

    private function subtreeHeight(int $categoryId): int
    {
        $height = 1;
        $queue = [$categoryId];

        while (!empty($queue)) {
            $children = Category::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($children)) {
                break;
            }

            $height++;
            $queue = $children;
        }

        return $height;
    }

    private function levelLabel(int $level): string
    {
        return match ($level) {
            1 => 'Danh mục cha',
            2 => 'Danh mục con',
            3 => 'Danh mục cháu',
            default => 'Danh mục',
        };
    }
}
