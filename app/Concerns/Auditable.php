<?php

namespace App\Concerns;

use App\Models\AuditLog;

trait Auditable
{
    /** @var array<string> Fields never written to the audit log */
    protected array $auditExclude = ['password', 'remember_token', 'invite_token', 'updated_at'];

    public static function bootAuditable(): void
    {
        static::created(function (self $model) {
            AuditLog::record('created', $model);
        });

        static::updated(function (self $model) {
            if ($model->isDirty()) {
                AuditLog::record('updated', $model, $model->getOriginal());
            }
        });

        static::deleted(function (self $model) {
            AuditLog::record('deleted', $model);
        });
    }
}
