<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FrontendProductSearch
{
    /**
     * Các từ bổ nghĩa thường gặp khi khách tìm kiếm, không nên bắt buộc phải có trong tên/slugs.
     *
     * @var array<int, string>
     */
    private const OPTIONAL_TOKENS = [
        'dep',
        'xinh',
        'xin',
        'sang',
        'chanh',
        'hot',
        'trend',
        'trendy',
        'moi',
        'new',
        'tot',
        'xin',
        're',
        'mem',
        'cao',
        'cap',
        'sieu',
        'sang',
        'xuat',
        'khau',
        'dang',
        'yeu',
        'de',
        'thuong',
        'chat',
        'luong',
        'vip',
        'fake',
        'loai',
        'shop',
    ];

    public static function normalize(?string $value): string
    {
        $value = self::stripVietnamese((string) $value);
        $value = Str::lower($value);
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    private static function stripVietnamese(string $value): string
    {
        return strtr($value, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
            'À' => 'A', 'Á' => 'A', 'Ạ' => 'A', 'Ả' => 'A', 'Ã' => 'A',
            'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ậ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A',
            'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ặ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A',
            'È' => 'E', 'É' => 'E', 'Ẹ' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E',
            'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ệ' => 'E', 'Ể' => 'E', 'Ễ' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Ị' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ọ' => 'O', 'Ỏ' => 'O', 'Õ' => 'O',
            'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ộ' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O',
            'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ợ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Ụ' => 'U', 'Ủ' => 'U', 'Ũ' => 'U',
            'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ự' => 'U', 'Ử' => 'U', 'Ữ' => 'U',
            'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỵ' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y',
            'Đ' => 'D',
        ]);
    }

    public static function applyToQuery(Builder $query, ?string $rawQuery): Builder
    {
        $normalizedQuery = self::normalize($rawQuery);

        if ($normalizedQuery === '') {
            return $query;
        }

        return self::applySearchConditions($query, $rawQuery, $normalizedQuery);
    }

    public static function suggestions(Builder $query, ?string $rawQuery, ?int $limit = null, int $offset = 0): Collection
    {
        $normalizedQuery = self::normalize($rawQuery);

        if ($normalizedQuery === '') {
            return collect();
        }

        $products = self::suggestionQuery(clone $query, $rawQuery, $normalizedQuery)
            ->select(['id', 'name', 'slug', 'price'])
            ->offset(max(0, $offset));

        if ($limit !== null) {
            $products->limit(max(1, $limit));
        }

        return $products->get();
    }

    public static function suggestionsCount(Builder $query, ?string $rawQuery): int
    {
        $normalizedQuery = self::normalize($rawQuery);

        if ($normalizedQuery === '') {
            return 0;
        }

        return (int) self::applySearchConditions(clone $query, $rawQuery, $normalizedQuery)->count();
    }

    private static function suggestionQuery(Builder $query, ?string $rawQuery, string $normalizedQuery): Builder
    {
        return self::applySearchConditions($query, $rawQuery, $normalizedQuery)
            ->orderByRaw(
                'CASE
                    WHEN slug LIKE ? THEN 0
                    WHEN slug LIKE ? THEN 1
                    ELSE 2
                END',
                [
                    self::slugSearchPrefix($normalizedQuery),
                    self::slugSearchContains($normalizedQuery),
                ]
            )
            ->orderByDesc('is_featured')
            ->orderByDesc('sold_count')
            ->orderByDesc('id');
    }

    private static function applySearchConditions(Builder $query, ?string $rawQuery, string $normalizedQuery): Builder
    {
        $tokens = self::tokens($normalizedQuery);
        $significantTokens = self::significantTokens($tokens);
        $nameTokens = self::rawTokens($rawQuery);
        $slugQuery = self::slugSearchContains($normalizedQuery);
        $significantSlugQuery = self::slugSearchContains(implode(' ', $significantTokens));
        $fallbackPhrases = self::fallbackPhrases($tokens);
        $significantFallbackPhrases = self::fallbackPhrases($significantTokens);
        $rawQuery = trim((string) $rawQuery);

        return $query->where(function (Builder $searchQuery) use ($rawQuery, $slugQuery, $significantSlugQuery, $tokens, $significantTokens, $nameTokens, $fallbackPhrases, $significantFallbackPhrases) {
            if ($rawQuery !== '') {
                $searchQuery->where('name', 'like', '%' . $rawQuery . '%');

                $searchQuery->orWhere(function (Builder $nameBuilder) use ($nameTokens) {
                    foreach ($nameTokens as $token) {
                        $nameBuilder->where('name', 'like', '%' . $token . '%');
                    }
                });

                $searchQuery->orWhere(function (Builder $fallbackNameBuilder) use ($fallbackPhrases, $significantFallbackPhrases) {
                    foreach (array_merge($fallbackPhrases, $significantFallbackPhrases) as $phrase) {
                        $fallbackNameBuilder->orWhere('name', 'like', '%' . $phrase . '%');
                    }
                });

                if ($significantTokens !== $tokens) {
                    $searchQuery->orWhere(function (Builder $nameBuilder) use ($significantTokens) {
                        foreach ($significantTokens as $token) {
                            $nameBuilder->where('slug', 'like', '%' . $token . '%');
                        }
                    });
                }

                $searchQuery->orWhere(function (Builder $slugBuilder) use ($slugQuery, $significantSlugQuery, $tokens, $significantTokens, $fallbackPhrases, $significantFallbackPhrases) {
                    $slugBuilder->where('slug', 'like', $slugQuery);

                    foreach ($tokens as $token) {
                        $slugBuilder->where('slug', 'like', '%' . $token . '%');
                    }

                    if ($significantTokens !== $tokens && $significantSlugQuery !== '%%') {
                        $slugBuilder->orWhere(function (Builder $significantSlugBuilder) use ($significantSlugQuery, $significantTokens) {
                            $significantSlugBuilder->where('slug', 'like', $significantSlugQuery);

                            foreach ($significantTokens as $token) {
                                $significantSlugBuilder->where('slug', 'like', '%' . $token . '%');
                            }
                        });
                    }

                    foreach (array_merge($fallbackPhrases, $significantFallbackPhrases) as $phrase) {
                        $slugBuilder->orWhere('slug', 'like', self::slugSearchContains($phrase));
                    }
                });

                return;
            }

            $searchQuery->where(function (Builder $slugBuilder) use ($slugQuery, $tokens) {
                $slugBuilder->where('slug', 'like', $slugQuery);

                foreach ($tokens as $token) {
                    $slugBuilder->where('slug', 'like', '%' . $token . '%');
                }
            });
        });
    }

    /**
     * @return array<int, string>
     */
    private static function tokens(string $normalizedQuery): array
    {
        return array_values(array_filter(explode(' ', trim($normalizedQuery))));
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<int, string>
     */
    private static function significantTokens(array $tokens): array
    {
        $filtered = array_values(array_filter($tokens, function (string $token) {
            if (mb_strlen($token) <= 1) {
                return false;
            }

            return ! in_array($token, self::OPTIONAL_TOKENS, true);
        }));

        return $filtered !== [] ? $filtered : $tokens;
    }

    /**
     * @return array<int, string>
     */
    private static function rawTokens(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        return array_values(array_filter(preg_split('/\s+/u', $value) ?: [], function (string $token) {
            return mb_strlen(trim($token)) > 1;
        }));
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<int, string>
     */
    private static function fallbackPhrases(array $tokens): array
    {
        $phrases = [];
        $tokenCount = count($tokens);

        if ($tokenCount < 3) {
            return [];
        }

        for ($length = $tokenCount - 1; $length >= 1; $length--) {
            $phrase = trim(implode(' ', array_slice($tokens, 0, $length)));

            if ($phrase !== '') {
                $phrases[] = $phrase;
            }
        }

        return array_values(array_unique($phrases));
    }

    private static function slugSearchContains(string $normalizedQuery): string
    {
        return '%' . str_replace(' ', '%', Str::slug($normalizedQuery, ' ')) . '%';
    }

    private static function slugSearchPrefix(string $normalizedQuery): string
    {
        return str_replace(' ', '%', Str::slug($normalizedQuery, ' ')) . '%';
    }
}
