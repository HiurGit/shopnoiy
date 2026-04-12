<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'product_target_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('product_target_id')->nullable()->after('image_url');
                $table->index('product_target_id', 'idx_categories_target_id');
            });
        }

        $this->migrateCategoryTargetsFromProducts();

        Schema::table('categories', function (Blueprint $table) {
            try {
                $table->foreign('product_target_id', 'fk_categories_product_target')
                    ->references('id')
                    ->on('product_targets')
                    ->nullOnDelete();
            } catch (\Throwable $exception) {
                // Ignore if the foreign key already exists.
            }
        });

        if (Schema::hasColumn('products', 'product_target_id')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_products_product_target');
                } catch (\Throwable $exception) {
                    // Ignore missing/duplicate foreign key state.
                }

                try {
                    $table->dropIndex('idx_products_target_id');
                } catch (\Throwable $exception) {
                    // Ignore missing index state.
                }
            });

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('product_target_id');
            });
        }

        if (Schema::hasColumn('products', 'target_gender')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('target_gender');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'target_gender')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('target_gender', 20)->default('female')->after('brand');
            });
        }

        if (!Schema::hasColumn('products', 'product_target_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('product_target_id')->nullable()->after('target_gender');
                $table->index('product_target_id', 'idx_products_target_id');
            });
        }

        $this->restoreProductTargetsFromCategories();

        Schema::table('products', function (Blueprint $table) {
            try {
                $table->foreign('product_target_id', 'fk_products_product_target')
                    ->references('id')
                    ->on('product_targets')
                    ->nullOnDelete();
            } catch (\Throwable $exception) {
                // Ignore if the foreign key already exists.
            }
        });

        if (Schema::hasColumn('categories', 'product_target_id')) {
            Schema::table('categories', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_categories_product_target');
                } catch (\Throwable $exception) {
                    // Ignore missing foreign key state.
                }

                try {
                    $table->dropIndex('idx_categories_target_id');
                } catch (\Throwable $exception) {
                    // Ignore missing index state.
                }
            });

            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('product_target_id');
            });
        }
    }

    private function migrateCategoryTargetsFromProducts(): void
    {
        if (!Schema::hasTable('categories') || !Schema::hasTable('products') || !Schema::hasTable('product_targets')) {
            return;
        }

        if (!Schema::hasColumn('products', 'product_target_id')) {
            return;
        }

        $winnerByCategory = DB::table('products')
            ->selectRaw('category_id, product_target_id, COUNT(*) as aggregate')
            ->whereNotNull('category_id')
            ->whereNotNull('product_target_id')
            ->groupBy('category_id', 'product_target_id')
            ->orderBy('category_id')
            ->orderByDesc('aggregate')
            ->get()
            ->groupBy('category_id')
            ->map(function (Collection $rows) {
                return (int) ($rows->sortByDesc('aggregate')->first()->product_target_id ?? 0);
            });

        foreach ($winnerByCategory as $categoryId => $targetId) {
            if ($targetId > 0) {
                DB::table('categories')->where('id', (int) $categoryId)->update(['product_target_id' => $targetId]);
            }
        }

        $categories = DB::table('categories')
            ->select(['id', 'parent_id', 'product_target_id'])
            ->orderByDesc('parent_id')
            ->orderByDesc('id')
            ->get();

        $targetsByCategory = [];
        foreach ($categories as $category) {
            $targetsByCategory[(int) $category->id] = $category->product_target_id ? (int) $category->product_target_id : null;
        }

        $childrenByParent = $categories->groupBy(fn ($category) => $category->parent_id ?? 0);
        $didChange = true;

        while ($didChange) {
            $didChange = false;

            foreach ($categories as $category) {
                $categoryId = (int) $category->id;
                if (!is_null($targetsByCategory[$categoryId] ?? null)) {
                    continue;
                }

                $childTargets = collect($childrenByParent->get($categoryId, collect()))
                    ->map(fn ($child) => $targetsByCategory[(int) $child->id] ?? null)
                    ->filter(fn ($targetId) => !is_null($targetId))
                    ->unique()
                    ->values();

                if ($childTargets->count() === 1) {
                    $targetId = (int) $childTargets->first();
                    DB::table('categories')->where('id', $categoryId)->update(['product_target_id' => $targetId]);
                    $targetsByCategory[$categoryId] = $targetId;
                    $didChange = true;
                }
            }
        }
    }

    private function restoreProductTargetsFromCategories(): void
    {
        if (!Schema::hasTable('categories') || !Schema::hasTable('products')) {
            return;
        }

        $categories = DB::table('categories')
            ->select(['id', 'product_target_id'])
            ->whereNotNull('product_target_id')
            ->get()
            ->keyBy('id');

        $targets = DB::table('product_targets')->pluck('slug', 'id');

        DB::table('products')
            ->select(['id', 'category_id'])
            ->orderBy('id')
            ->chunkById(500, function ($products) use ($categories, $targets) {
                foreach ($products as $product) {
                    $targetId = (int) ($categories[(int) $product->category_id]->product_target_id ?? 0);
                    $targetSlug = $targetId > 0 ? (string) ($targets[$targetId] ?? 'female') : 'female';

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'product_target_id' => $targetId > 0 ? $targetId : null,
                            'target_gender' => $targetSlug !== '' ? $targetSlug : 'female',
                        ]);
                }
            });
    }
};
