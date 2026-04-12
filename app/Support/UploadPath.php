<?php

namespace App\Support;

class UploadPath
{
    public static function baseDir(): string
    {
        return config('uploads.base_dir', 'uploads');
    }

    public static function folder(string $key): string
    {
        return config("uploads.folders.{$key}", $key);
    }

    public static function relative(string $key): string
    {
        return trim(self::baseDir() . '/' . self::folder($key), '/');
    }

    public static function absolute(string $key): string
    {
        return public_path(self::relative($key));
    }

    public static function urlPrefix(string $key): string
    {
        return asset(self::relative($key));
    }
}
