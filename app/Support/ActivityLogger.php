<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(?object $user, string $action, string $description, array $extra = []): void
    {
        ActivityLog::query()->create([
            'user_id' => $user?->id,
            'user_name' => $user?->full_name ?: null,
            'user_email' => $user?->email ?: null,
            'user_role' => $user?->role ?: null,
            'action' => $action,
            'description' => $description,
            'route_name' => $extra['route_name'] ?? null,
            'method' => $extra['method'] ?? null,
            'url' => $extra['url'] ?? null,
            'subject_type' => $extra['subject_type'] ?? null,
            'subject_id' => isset($extra['subject_id']) ? (string) $extra['subject_id'] : null,
            'properties' => $extra['properties'] ?? null,
            'ip_address' => $extra['ip_address'] ?? null,
            'user_agent' => $extra['user_agent'] ?? null,
            'created_at' => now(),
        ]);
    }

    public static function logBackendRequest(Request $request, ?object $user): void
    {
        $route = $request->route();
        $routeName = $route?->getName();
        $method = strtoupper($request->method());

        if (!$routeName || !in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if (in_array($routeName, ['backend.logout', 'backend.login.submit'], true)) {
            return;
        }

        [$subjectType, $subjectId] = self::resolveSubject($request);

        self::log(
            $user,
            self::resolveAction($method),
            self::buildDescription($routeName, $method, $subjectType, $subjectId),
            [
                'route_name' => $routeName,
                'method' => $method,
                'url' => $request->fullUrl(),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'properties' => [
                    'payload' => self::sanitizePayload($request->except([
                        '_token',
                        '_method',
                        'password',
                        'password_hash',
                        'password_confirmation',
                        'remember',
                    ])),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );
    }

    private static function resolveAction(string $method): string
    {
        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'action',
        };
    }

    private static function buildDescription(?string $routeName, string $method, ?string $subjectType, string|int|null $subjectId): string
    {
        $module = $routeName ? str_replace('backend.', '', $routeName) : 'backend';
        $module = str_replace(['.', '-'], ' / ', $module);

        $verb = match ($method) {
            'POST' => 'Tạo mới',
            'PUT', 'PATCH' => 'Cập nhật',
            'DELETE' => 'Xóa',
            default => 'Thao tác',
        };

        $target = $subjectType ? $subjectType . ($subjectId !== null ? ' #' . $subjectId : '') : $module;

        return trim($verb . ' ' . $target);
    }

    /**
     * @return array{0: ?string, 1: string|int|null}
     */
    private static function resolveSubject(Request $request): array
    {
        foreach (($request->route()?->parameters() ?? []) as $parameter) {
            if ($parameter instanceof Model) {
                return [class_basename($parameter), $parameter->getKey()];
            }

            if (is_scalar($parameter)) {
                return ['record', (string) $parameter];
            }
        }

        return [null, null];
    }

    private static function sanitizePayload(mixed $value): mixed
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $key => $item) {
                if (is_numeric($key)) {
                    $sanitized[$key] = self::sanitizePayload($item);
                    continue;
                }

                if (in_array((string) $key, ['images', 'image', 'file', 'files'], true)) {
                    $sanitized[$key] = '[uploaded files]';
                    continue;
                }

                $sanitized[$key] = self::sanitizePayload($item);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return mb_strlen($value) > 180 ? mb_substr($value, 0, 177) . '...' : $value;
        }

        if (is_object($value)) {
            return '[object]';
        }

        return $value;
    }
}
