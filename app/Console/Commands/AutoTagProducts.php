<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AutoTagProducts extends Command
{
    private const TAG_DEFINITIONS = [
        'mac_hang_ngay' => ['order' => 1, 'label' => 'mặc hằng ngày'],
        'thoai_mai' => ['order' => 2, 'label' => 'thoải mái'],
        'thoang_khi' => ['order' => 3, 'label' => 'thoáng khí'],
        'khong_gong' => ['order' => 4, 'label' => 'không gọng'],
        'co_gong' => ['order' => 5, 'label' => 'có gọng'],
        'nang_nguc' => ['order' => 6, 'label' => 'nâng ngực'],
        'tao_khe' => ['order' => 7, 'label' => 'tạo khe'],
        'dinh_hinh' => ['order' => 8, 'label' => 'định hình'],
        'gen_bung' => ['order' => 9, 'label' => 'gen bụng'],
        'nang_mong' => ['order' => 10, 'label' => 'nâng mông'],
        'khong_lo_vien' => ['order' => 11, 'label' => 'không lộ viền'],
        'the_thao' => ['order' => 12, 'label' => 'thể thao'],
        'croptop' => ['order' => 13, 'label' => 'croptop'],
        'mac_vay' => ['order' => 14, 'label' => 'mặc váy'],
        'ao_dan' => ['order' => 16, 'label' => 'áo dán'],
        'gai_truoc' => ['order' => 17, 'label' => 'gài trước'],
        'quyen_ru' => ['order' => 18, 'label' => 'quyến rũ'],
        'ren' => ['order' => 19, 'label' => 'ren'],
        'su_tron' => ['order' => 20, 'label' => 'su trơn'],
        'cotton' => ['order' => 21, 'label' => 'cotton'],
        'tuoi_teen' => ['order' => 23, 'label' => 'tuổi teen'],
        'de_thuong' => ['order' => 24, 'label' => 'dễ thương'],
        'giu_am' => ['order' => 25, 'label' => 'giữ ấm'],
        'mem_min' => ['order' => 26, 'label' => 'mềm mịn'],
        'om_dang' => ['order' => 27, 'label' => 'ôm dáng'],
        'thoang_mat' => ['order' => 28, 'label' => 'thoáng mát'],
        'sieu_em' => ['order' => 29, 'label' => 'siêu êm'],
        'tinh_te' => ['order' => 30, 'label' => 'tinh tế'],
        'sang_trong' => ['order' => 31, 'label' => 'sang trọng'],
        'chuan_form' => ['order' => 32, 'label' => 'chuẩn form'],
        'co_gian_tot' => ['order' => 33, 'label' => 'co giãn tốt'],
        'nhe_tenh' => ['order' => 34, 'label' => 'nhẹ tênh'],
        'dep_xin' => ['order' => 35, 'label' => 'đẹp xịn'],
        'cuc_thich' => ['order' => 36, 'label' => 'cực thích'],
        'rat_xinh' => ['order' => 37, 'label' => 'rất xinh'],
        'mac_suong' => ['order' => 38, 'label' => 'mặc sướng'],
        'ton_dang' => ['order' => 39, 'label' => 'tôn dáng'],
    ];

    protected $signature = 'products:auto-tag';

    protected $description = 'Auto-assign suggested tags to existing products based on name, category, and description.';

    public function handle(): int
    {
        $tagIdsByKey = [];

        foreach (self::TAG_DEFINITIONS as $key => $definition) {
            $tagId = ProductTag::query()
                ->where('sort_order', $definition['order'])
                ->value('id');

            if ($tagId) {
                $tagIdsByKey[$key] = (int) $tagId;
            }
        }

        $products = Product::query()
            ->with('category:id,name', 'tags:id,name')
            ->orderBy('id')
            ->get();

        $tagUsage = [];
        $productsUpdated = 0;

        foreach ($products as $product) {
            $tagKeys = $this->suggestTagsForProduct($product);
            $tagIds = collect($tagKeys)
                ->map(fn (string $key) => $tagIdsByKey[$key] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($tagIds) === 0) {
                continue;
            }

            $before = $product->tags->pluck('id')->map(fn ($id) => (int) $id)->all();
            $after = array_values(array_unique(array_merge($before, $tagIds)));

            if ($before !== $after) {
                $product->tags()->sync($after);
                $productsUpdated++;
            }

            foreach ($tagKeys as $tagKey) {
                $tagUsage[$tagKey] = ($tagUsage[$tagKey] ?? 0) + 1;
            }
        }

        $this->info('Đã gắn/gợi ý tag cho sản phẩm hiện có.');
        $this->line('Sản phẩm được cập nhật: ' . $productsUpdated);
        $this->line('Tổng sản phẩm quét: ' . $products->count());

        if (!empty($tagUsage)) {
            arsort($tagUsage);
            $this->newLine();
            $this->line('Top tag được gắn:');

            foreach (array_slice($tagUsage, 0, 12, true) as $tagKey => $count) {
                $label = self::TAG_DEFINITIONS[$tagKey]['label'] ?? $tagKey;
                $this->line('- ' . $label . ': ' . $count);
            }
        }

        return self::SUCCESS;
    }

    private function suggestTagsForProduct(Product $product): array
    {
        $name = (string) $product->name;
        $category = (string) ($product->category->name ?? '');
        $description = (string) ($product->description ?? '');
        $haystack = $this->normalizeText($name . ' ' . $category . ' ' . $description);

        $tags = [];

        $isBra = $this->containsAny($haystack, ['ao lot', 'ao bra', 'bra', 'ao nguc', 'ao su', 'ao nang nguc', 'ao khong gong']);
        $isPanty = $this->containsAny($haystack, ['quan lot', 'quan su', 'quan tam giac', 'quan lot ren', 'quan lot cotton', 'quan lot lua', 'quan lot nu']);
        $isShapewear = $this->containsAny($haystack, ['gen', 'dinh hinh', 'nit bung', 'siet eo', 'nang mong', 'don mong']);
        $isBacklessOrDress = $this->containsAny($haystack, ['ho lung', 'mac vay', 'quay', 'multiway', 'khoet u', 'cai truoc', 'day lung trong']);
        $isSticker = $this->containsAny($haystack, ['ao dan', 'mieng dan', 'dan nguc', 'nhu hoa']);
        $isSport = $this->containsAny($haystack, ['the thao', 'gym', 'yoga', 'aerobic', 'sport', 'croptop']);
        $isWarm = $this->containsAny($haystack, ['giu am', 'lot long', 'long cuu', 'nhiet', 'mua dong', 'len']);
        $isCute = $this->containsAny($haystack, ['de thuong', 'gau', 'meo', 'no', 'pastel', 'hoa hong', 'nu sinh', 'hoc sinh']);
        $hasSoftMaterial = $this->containsAny($haystack, ['mem', 'mem mai', 'mem min', 'lụa', 'lua', 'cotton', 'thun lanh', 'su non', 'moi da']);
        $hasCoolMaterial = $this->containsAny($haystack, ['thoang mat', 'mat lanh', 'lanh', 'thong hoi', 'thoang khi', 'cotton', 'su']);
        $hasStretchSignal = $this->containsAny($haystack, ['co gian', '4 chieu', 'dan hoi', 'om sat', 'fit']);
        $hasLuxurySignal = $this->containsAny($haystack, ['cao cap', 'sang trong', 'tinh te', 'quyen ru', 'ren', 'goi cam']);
        $hasPrettySignal = $this->containsAny($haystack, ['xinh', 'dep', 'yeu', 'ren', 'dieu da', 'nu tinh']);

        if ($isBra || $isPanty || $isSport) {
            $tags[] = 'mac_hang_ngay';
            $tags[] = 'thoai_mai';
        }

        if ($this->containsAny($haystack, ['thoang khi', 'thong hoi', 'mat lanh'])) {
            $tags[] = 'thoang_khi';
        }

        if ($this->containsAny($haystack, ['khong gong'])) {
            $tags[] = 'khong_gong';
        } elseif ($this->containsAny($haystack, ['co gong', 'lot gong']) || str_contains($this->normalizeText($category), ' gong ')) {
            $tags[] = 'co_gong';
        }

        if ($this->containsAny($haystack, ['nang nguc', 'push up', 'sieu nang', 'dem day', '5p', '6p'])) {
            $tags[] = 'nang_nguc';
        }

        if ($this->containsAny($haystack, ['tao khe', 'cai truoc', 'push up', 'gom nguc'])) {
            $tags[] = 'tao_khe';
        }

        if ($isShapewear || $this->containsAny($haystack, ['dinh hinh', 'thon gon', 'siet eo'])) {
            $tags[] = 'dinh_hinh';
        }

        if ($this->containsAny($haystack, ['gen bung', 'nit bung', 'lung cao gen', 'eo'])) {
            $tags[] = 'gen_bung';
        }

        if ($this->containsAny($haystack, ['nang mong', 'don mong', 'vong ba'])) {
            $tags[] = 'nang_mong';
        }

        if ($this->containsAny($haystack, ['khong lo vien', 'khong duong may', 'tang hinh'])) {
            $tags[] = 'khong_lo_vien';
        }

        if ($isSport) {
            $tags[] = 'the_thao';
            $tags[] = 'croptop';
        }

        if ($isBacklessOrDress || $isSticker) {
            $tags[] = 'mac_vay';
        }

        if ($isSticker) {
            $tags[] = 'ao_dan';
        }

        if ($this->containsAny($haystack, ['gai truoc', 'cai truoc'])) {
            $tags[] = 'gai_truoc';
        }

        $isTeenProduct = $this->containsAny($haystack, ['hoc sinh', 'nu sinh', 'teen']);
        $shouldDefaultToSeductive = ($isBra || $isPanty || $isShapewear || $isBacklessOrDress || $isSticker)
            && ! $isSport
            && ! $isWarm
            && ! $isTeenProduct;

        if ($shouldDefaultToSeductive || $this->containsAny($haystack, ['goi cam', 'xuyen thau', 'lot khe', 'sexy', 'tao khe'])) {
            $tags[] = 'quyen_ru';
        }

        if ($this->containsAny($haystack, ['ren'])) {
            $tags[] = 'ren';
        }

        if ($this->containsAny($haystack, [' chat su ', ' ao su ', ' quan su ', 'silicon', 'su non']) || str_contains($this->normalizeText($category), ' su ')) {
            $tags[] = 'su_tron';
        }

        if ($this->containsAny($haystack, ['cotton'])) {
            $tags[] = 'cotton';
        }

        if ($isTeenProduct) {
            $tags[] = 'tuoi_teen';
        }

        if ($isCute) {
            $tags[] = 'de_thuong';
        }

        if ($isWarm) {
            $tags[] = 'giu_am';
        }

        if ($hasSoftMaterial || ($isBra && ! $isSport) || ($isPanty && ! $isWarm)) {
            $tags[] = 'mem_min';
        }

        if ($isBra || $isShapewear || $this->containsAny($haystack, ['om dang', 'om sat', 'body', 'form'])) {
            $tags[] = 'om_dang';
        }

        if ($hasCoolMaterial || $this->containsAny($haystack, ['thoang mat'])) {
            $tags[] = 'thoang_mat';
        }

        if ($hasSoftMaterial || $this->containsAny($haystack, ['sieu em', 'em ai', 'nhe nhu khong'])) {
            $tags[] = 'sieu_em';
        }

        if ($hasLuxurySignal || $isBacklessOrDress || $isSticker) {
            $tags[] = 'tinh_te';
        }

        if (($hasLuxurySignal && ! $isSport) || $this->containsAny($haystack, ['sang trong'])) {
            $tags[] = 'sang_trong';
        }

        if ($isBra || $isShapewear || $this->containsAny($haystack, ['chuan form', 'dung form', 'form dep'])) {
            $tags[] = 'chuan_form';
        }

        if ($hasStretchSignal || $isSport || $isShapewear || $this->containsAny($haystack, ['co gian tot'])) {
            $tags[] = 'co_gian_tot';
        }

        if (($isBra || $isPanty) && ! $isShapewear && ! $isWarm) {
            $tags[] = 'nhe_tenh';
        }

        if (($hasPrettySignal || $hasLuxurySignal) && ! $isWarm) {
            $tags[] = 'dep_xin';
        }

        if (($isBra || $isPanty) && ! $isWarm) {
            $tags[] = 'cuc_thich';
            $tags[] = 'mac_suong';
        }

        if (($hasPrettySignal || $isCute || $this->containsAny($haystack, ['rat xinh'])) && ! $isWarm) {
            $tags[] = 'rat_xinh';
        }

        if ($isBra || $isShapewear || $this->containsAny($haystack, ['ton dang', 'hack dang', 'thon gon'])) {
            $tags[] = 'ton_dang';
        }

        return array_values(array_unique(array_filter($tags)));
    }

    private function normalizeText(string $value): string
    {
        $value = Str::ascii($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? '';

        return ' ' . trim($value) . ' ';
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, ' ' . trim($needle) . ' ')) {
                return true;
            }
        }

        return false;
    }
}
