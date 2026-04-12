<?php

use App\Support\BasicCatalogImporter;
use App\Support\CategoryImageSyncer;
use App\Support\UploadPath;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('uploads:init', function () {
    $base = UploadPath::baseDir();
    $folders = array_keys(config('uploads.folders', []));

    $this->info("Initializing upload folders under {$base} ...");

    if (!is_dir(public_path($base))) {
        mkdir(public_path($base), 0755, true);
    }

    foreach ($folders as $key) {
        $path = UploadPath::absolute($key);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            $this->line("Created: {$path}");
        } else {
            $this->line("Exists:  {$path}");
        }
    }

    $this->info('Upload folders initialized.');
})->purpose('Create upload directories in uploads');

Artisan::command('catalog:import-basic-data {--source=basic-data-Nhung.js} {--limit=}', function () {
    $source = (string) $this->option('source');
    $limitOption = $this->option('limit');
    $limit = is_null($limitOption) || $limitOption === '' ? null : max(0, (int) $limitOption);
    $sourcePath = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $source));

    $this->info('Dang import catalog tu: ' . $sourcePath);
    $this->line('Se xoa du lieu catalog mau hien co, tao lai danh muc, them san pham va tai anh ve uploads/products.');
    if (!is_null($limit)) {
        $this->line('Gioi han import: ' . $limit . ' san pham dau tien.');
    }

    /** @var BasicCatalogImporter $importer */
    $importer = app(BasicCatalogImporter::class);
    $stats = $importer->import($sourcePath, $limit);

    $this->info('Import hoan tat.');
    $this->line('Danh muc cha: ' . $stats['categories']);
    $this->line('Danh muc con: ' . $stats['subcategories']);
    $this->line('San pham: ' . $stats['products']);
    $this->line('Anh da luu local: ' . $stats['images']);

    $failures = $stats['image_failures'] ?? [];
    if (count($failures) > 0) {
        $this->warn('Anh tai loi: ' . count($failures));
        foreach (array_slice($failures, 0, 10) as $failure) {
            $this->line('- ' . $failure['product'] . ' | ' . $failure['url']);
        }
        if (count($failures) > 10) {
            $this->line('... va ' . (count($failures) - 10) . ' anh khac.');
        }
    }
})->purpose('Import san pham that tu basic-data-Nhung.js, tai anh ve local va thay the du lieu catalog mau');

Artisan::command('categories:sync-images-from-products {--overwrite=1}', function () {
    $overwrite = (bool) ((int) $this->option('overwrite'));

    /** @var CategoryImageSyncer $syncer */
    $syncer = app(CategoryImageSyncer::class);
    $stats = $syncer->sync($overwrite);

    $this->info('Dong bo anh danh muc hoan tat.');
    $this->line('Da cap nhat: ' . $stats['updated']);
    $this->line('Bo qua: ' . $stats['skipped']);
    $this->line('Khong tim thay anh nguon: ' . $stats['missing_source']);
})->purpose('Lay anh san pham lam anh danh muc co dinh va luu vao uploads/categories');
