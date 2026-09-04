<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AuditObserver
{
    private const HIDDEN = ['password', 'remember_token', 'token', 'secret'];

    public function created(Model $model): void
    {
        $this->record('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->record('updated', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, $model->getOriginal(), null);
    }

    private function record(string $event, Model $model, ?array $old, ?array $new): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }
        $request = app()->bound('request') ? request() : null;
        $schoolId = $model->getAttribute('school_id') ?? auth()->user()?->school_id;
        AuditLog::create([
            'school_id' => $schoolId,
            'actor_id' => auth()->id(),
            'event' => class_basename($model).'.'.$event,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values' => $old ? Arr::except($old, self::HIDDEN) : null,
            'new_values' => $new ? Arr::except($new, self::HIDDEN) : null,
            'ip_address' => $request?->ip(),
            'request_id' => $request?->headers->get('X-Request-ID'),
            'created_at' => now(),
        ]);
    }
}
