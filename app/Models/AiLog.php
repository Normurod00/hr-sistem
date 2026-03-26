<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AiLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Только created_at

    protected $fillable = [
        'application_id',
        'operation',
        'status',
        'duration_ms',
        'message',
        'request_data',
        'response_data',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'request_data' => 'array',
            'response_data' => 'array',
        ];
    }

    // ========== Operation Constants ==========

    const OP_PARSE_RESUME = 'parse_resume';
    const OP_PARSE_FILE = 'parse_file';
    const OP_ANALYZE = 'analyze';
    const OP_MATCH_SCORE = 'match_score';
    const OP_GENERATE_QUESTIONS = 'generate_questions';
    const OP_BUILD_PROFILE = 'build_profile';

    // ========== Status Constants ==========

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    // ========== Relationships ==========

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // ========== Scopes ==========

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByOperation(Builder $query, string $operation): Builder
    {
        return $query->where('operation', $operation);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ========== Accessors ==========

    public function getIsSuccessAttribute(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function getIsErrorAttribute(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getDurationFormattedAttribute(): string
    {
        if ($this->duration_ms === null) {
            return '—';
        }

        if ($this->duration_ms >= 1000) {
            return round($this->duration_ms / 1000, 2) . ' сек';
        }

        return $this->duration_ms . ' мс';
    }

    public function getOperationLabelAttribute(): string
    {
        return match ($this->operation) {
            self::OP_PARSE_RESUME => 'Парсинг резюме',
            self::OP_PARSE_FILE => 'Парсинг файла',
            self::OP_ANALYZE => 'Анализ кандидата',
            self::OP_MATCH_SCORE => 'Расчёт совместимости',
            self::OP_GENERATE_QUESTIONS => 'Генерация вопросов',
            self::OP_BUILD_PROFILE => 'Построение профиля',
            default => $this->operation,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'Успешно',
            self::STATUS_ERROR => 'Ошибка',
            self::STATUS_PENDING => 'В процессе',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'green',
            self::STATUS_ERROR => 'red',
            self::STATUS_PENDING => 'yellow',
            default => 'gray',
        };
    }

    public function getStatusBgClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'bg-green-100 text-green-700',
            self::STATUS_ERROR => 'bg-red-100 text-red-700',
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    // ========== Factory Methods ==========

    public static function logStart(
        string $operation,
        ?int $applicationId = null,
        ?array $requestData = null
    ): self {
        return self::create([
            'application_id' => $applicationId,
            'operation' => $operation,
            'status' => self::STATUS_PENDING,
            'request_data' => $requestData,
        ]);
    }

    public function markSuccess(?array $responseData = null, ?int $durationMs = null): bool
    {
        return $this->update([
            'status' => self::STATUS_SUCCESS,
            'response_data' => $responseData,
            'duration_ms' => $durationMs,
        ]);
    }

    public function markError(string $message, ?int $durationMs = null): bool
    {
        return $this->update([
            'status' => self::STATUS_ERROR,
            'message' => $message,
            'duration_ms' => $durationMs,
        ]);
    }

    public static function logSuccess(
        string $operation,
        ?int $applicationId = null,
        ?array $requestData = null,
        ?array $responseData = null,
        ?int $durationMs = null,
        ?string $message = null
    ): self {
        return self::create([
            'application_id' => $applicationId,
            'operation' => $operation,
            'status' => self::STATUS_SUCCESS,
            'duration_ms' => $durationMs,
            'message' => $message,
            'request_data' => $requestData,
            'response_data' => $responseData,
        ]);
    }

    public static function logError(
        string $operation,
        string $message,
        ?int $applicationId = null,
        ?array $requestData = null,
        ?int $durationMs = null
    ): self {
        return self::create([
            'application_id' => $applicationId,
            'operation' => $operation,
            'status' => self::STATUS_ERROR,
            'message' => $message,
            'duration_ms' => $durationMs,
            'request_data' => $requestData,
        ]);
    }
}
