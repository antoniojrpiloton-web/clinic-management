<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Attributes that must never be written to the audit trail.
     *
     * @var list<string>
     */
    protected array $hiddenAttributes = ['password', 'remember_token'];

    /**
     * Record an audit log entry.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(string $event, string $description, ?Model $subject = null, array $properties = [], ?int $userId = null): AuditLog
    {
        $request = request();

        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'event' => $event,
            'description' => Str::limit($description, 250),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $this->sanitize($properties) ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 250, ''),
        ]);
    }

    /**
     * Record the changed attributes of an updated model.
     *
     * @param  array<string, mixed>  $original
     */
    public function logChanges(Model $subject, array $original, string $description): ?AuditLog
    {
        $changes = $subject->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return null;
        }

        return $this->log('updated', $description, $subject, [
            'old' => array_intersect_key($original, $changes),
            'new' => $changes,
        ]);
    }

    /**
     * Remove sensitive attributes recursively.
     *
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    protected function sanitize(array $properties): array
    {
        foreach ($properties as $key => $value) {
            if (in_array($key, $this->hiddenAttributes, true)) {
                $properties[$key] = '********';
            } elseif (is_array($value)) {
                $properties[$key] = $this->sanitize($value);
            }
        }

        return $properties;
    }
}
