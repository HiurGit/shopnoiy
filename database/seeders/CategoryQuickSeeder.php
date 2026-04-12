<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryQuickSeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Áo' => [
                'Áo lót trơn',
                'Áo lót ren',
                'Áo lót không gọng',
                'Áo lót có gọng',
                'Áo lót nâng ngực',
                'Áo lót thể thao',
                'Áo lót không dây',
                'Áo lót cho mẹ bầu',
            ],
            'Quần' => [
                'Quần lót cotton',
                'Quần lót ren',
                'Quần lót không viền',
                'Quần lót lọt khe',
                'Quần lót cạp cao',
                'Quần lót định hình',
            ],
            'Bộ đồ lót' => [
                'Bộ áo + quần đồng bộ',
                'Bộ đồ lót ren sexy',
                'Bộ đồ lót mặc ngủ',
            ],
            'Đồ mặc nhà / mặc ngủ' => [
                'Váy ngủ',
                'Đồ ngủ pijama',
                'Áo choàng ngủ',
            ],
            'Phụ kiện đồ lót' => [
                'Miếng dán ngực',
                'Dây áo lót trong suốt',
                'Mút nâng ngực',
                'Túi giặt đồ lót',
            ],
        ];

        $parentSort = 10;
        foreach ($tree as $parentName => $children) {
            $parent = $this->upsertCategory($parentName, null, $parentSort);

            $childSort = 10;
            foreach ($children as $childName) {
                $this->upsertCategory($childName, (int) $parent->id, $childSort);
                $childSort += 10;
            }

            $parentSort += 10;
        }
    }

    private function upsertCategory(string $name, ?int $parentId, int $sortOrder): Category
    {
        $category = Category::query()
            ->where('name', $name)
            ->where('parent_id', $parentId)
            ->first();

        if ($category) {
            $category->update([
                'sort_order' => $sortOrder,
                'status' => 'active',
            ]);

            return $category;
        }

        return Category::query()->create([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'sort_order' => $sortOrder,
            'status' => 'active',
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'danh-muc';
        $slug = $base;
        $i = 1;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}

