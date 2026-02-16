<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(string $action, array $context = [], ?Model $subject = null, ?array $actorOverride = null, ?Request $request = null): void
    {
        $actor = $this->resolveActor($actorOverride);
        $request = $request ?? request();

        AuditLog::create([
            'actor_type' => $actor['type'] ?? null,
            'actor_id' => $actor['id'] ?? null,
            'actor_name' => $actor['name'] ?? null,
            'actor_phone' => $actor['phone'] ?? null,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'route' => $request?->route()?->getName(),
            'method' => $request?->method(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'meta' => $context ?: null,
        ]);
    }

    private function resolveActor(?array $override = null): array
    {
        if ($override) {
            return $override;
        }

        if (Auth::check()) {
            $user = Auth::user();

            return [
                'type' => 'USER',
                'id' => $user->id,
                'name' => $user->name ?? $user->email,
                'phone' => $user->pharmacy?->phone ?? null,
            ];
        }

        return [];
    }
}
