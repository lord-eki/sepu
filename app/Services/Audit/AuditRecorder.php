<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditRecorder
{
    // Writes one audit log row for an action on a model, with the acting user and request details.
    public function record(string $action, Model $subject, ?User $by, array $old = [], array $new = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $by?->id,
            'action' => $action,
            'model_type' => $subject::class,
            'model_id' => $subject->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}