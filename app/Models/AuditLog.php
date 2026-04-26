<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model> */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model> */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /** @param array<string, mixed> $originalValues */
    public static function record(string $event, Model $auditable, array $originalValues = []): void
    {
        try {
            $request = request();
            $ip = $request->ip();
            $ua = $request->userAgent();
        } catch (\Throwable) {
            $ip = null;
            $ua = null;
        }

        $guard = auth('admin')->check() ? 'admin' : 'web';
        $authUser = auth($guard)->user();

        /** @var array<string> $exclude */
        $exclude = property_exists($auditable, 'auditExclude')
            ? (array) $auditable->auditExclude
            : ['password', 'remember_token', 'invite_token'];

        $filtered = fn (array $attrs) => array_diff_key($attrs, array_flip($exclude));

        static::create([
            'user_type' => $authUser ? get_class($authUser) : null,
            'user_id' => $authUser?->getKey(),
            'event' => $event,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $event === 'updated' ? $filtered($originalValues) : null,
            'new_values' => $event !== 'deleted' ? $filtered($auditable->getAttributes()) : null,
            'ip_address' => $ip,
            'user_agent' => $ua,
        ]);
    }
}
