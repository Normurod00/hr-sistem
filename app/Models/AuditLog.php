<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'employee_profile_id',
        'action',
        'resource_type',
        'resource_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    // ===== SCOPES =====

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByResourceType($query, string $type)
    {
        return $query->where('resource_type', $type);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ===== ACCESSORS =====

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'view' => 'Просмотр',
            'create' => 'Создание',
            'update' => 'Обновление',
            'delete' => 'Удаление',
            'export' => 'Экспорт',
            'ai_query' => 'AI запрос',
            'login' => 'Вход',
            'logout' => 'Выход',
            default => $this->action,
        };
    }

    public function getResourceTypeLabelAttribute(): string
    {
        return match ($this->resource_type) {
            'kpi_snapshot' => 'KPI',
            'conversation' => 'Разговор',
            'policy' => 'Политика',
            'employee_profile' => 'Профиль сотрудника',
            'recommendation' => 'Рекомендация',
            default => $this->resource_type,
        };
    }

    // ===== HELPERS =====

    public static function log(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): self {
        $user = auth()->user();
        $request = request();

        return self::create([
            'user_id' => $user?->id,
            'employee_profile_id' => $user?->employeeProfile?->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    public static function logView(string $resourceType, int $resourceId, ?array $metadata = null): self
    {
        return self::log('view', $resourceType, $resourceId, null, null, $metadata);
    }

    public static function logAiQuery(string $query, ?array $response = null): self
    {
        return self::log('ai_query', 'ai_conversation', null, null, null, [
            'query' => $query,
            'response_preview' => $response ? \Str::limit($response['content'] ?? '', 200) : null,
        ]);
    }
}
