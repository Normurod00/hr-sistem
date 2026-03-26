<?php

namespace App\Models;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'integration',
        'operation',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'duration_ms',
        'retry_count',
        'correlation_id',
        'triggered_by',
        'created_at',
    ];

    protected $casts = [
        'integration' => IntegrationType::class,
        'status' => IntegrationStatus::class,
        'request_data' => 'array',
        'response_data' => 'array',
        'created_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    // ===== SCOPES =====

    public function scopeByIntegration($query, IntegrationType $type)
    {
        return $query->where('integration', $type);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', IntegrationStatus::Success);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [IntegrationStatus::Error, IntegrationStatus::Timeout]);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // ===== ACCESSORS =====

    public function getIntegrationLabelAttribute(): string
    {
        return match ($this->integration) {
            IntegrationType::Kpi => 'KPI System',
            IntegrationType::Pulse => 'Pulse',
            IntegrationType::SmartOffice => 'Smart Office',
            IntegrationType::Iabs => 'iABS',
            IntegrationType::AiServer => 'AI Server',
            default => 'Unknown',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            IntegrationStatus::Pending => 'В процессе',
            IntegrationStatus::Success => 'Успешно',
            IntegrationStatus::Error => 'Ошибка',
            IntegrationStatus::Timeout => 'Таймаут',
            default => 'Неизвестно',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            IntegrationStatus::Pending => 'warning',
            IntegrationStatus::Success => 'success',
            IntegrationStatus::Error => 'danger',
            IntegrationStatus::Timeout => 'secondary',
            default => 'secondary',
        };
    }

    // ===== HELPERS =====

    public function isSuccessful(): bool
    {
        return $this->status === IntegrationStatus::Success;
    }

    public function isFailed(): bool
    {
        return in_array($this->status, [IntegrationStatus::Error, IntegrationStatus::Timeout]);
    }

    public static function logRequest(
        IntegrationType $integration,
        string $operation,
        ?array $requestData = null,
        ?int $triggeredBy = null
    ): self {
        return self::create([
            'integration' => $integration,
            'operation' => $operation,
            'status' => IntegrationStatus::Pending,
            'request_data' => $requestData,
            'correlation_id' => \Str::uuid()->toString(),
            'triggered_by' => $triggeredBy,
            'created_at' => now(),
        ]);
    }

    public function markSuccess(array $responseData, int $durationMs): void
    {
        $this->update([
            'status' => IntegrationStatus::Success,
            'response_data' => $responseData,
            'duration_ms' => $durationMs,
        ]);
    }

    public function markError(string $message, int $durationMs, int $retryCount = 0): void
    {
        $this->update([
            'status' => IntegrationStatus::Error,
            'error_message' => $message,
            'duration_ms' => $durationMs,
            'retry_count' => $retryCount,
        ]);
    }

    public function markTimeout(int $durationMs): void
    {
        $this->update([
            'status' => IntegrationStatus::Timeout,
            'error_message' => 'Request timed out',
            'duration_ms' => $durationMs,
        ]);
    }
}
