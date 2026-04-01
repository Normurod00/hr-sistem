<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'uploaded_by',
        'document_type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'parsed_text',
        'status',
        'analysis_result',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'analysis_result' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PARSED = 'parsed';
    const STATUS_FAILED = 'failed';

    const TYPE_CONTRACT = 'contract';
    const TYPE_DIPLOMA = 'diploma';
    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_ID_DOCUMENT = 'id_document';
    const TYPE_MEDICAL = 'medical';
    const TYPE_OTHER = 'other';

    // ========== Relationships ==========

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ========== Scopes ==========

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeParsed($query)
    {
        return $query->where('status', self::STATUS_PARSED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeForEmployee($query, int $employeeProfileId)
    {
        return $query->where('employee_profile_id', $employeeProfileId);
    }

    // ========== Accessors ==========

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'contract' => 'Трудовой договор',
            'diploma' => 'Диплом',
            'certificate' => 'Сертификат',
            'id_document' => 'Удостоверение личности',
            'medical' => 'Медицинская справка',
            'other' => 'Другой документ',
            default => 'Документ',
        };
    }

    public function getDocumentTypeIconAttribute(): string
    {
        return match ($this->document_type) {
            'contract' => 'bi-file-earmark-text',
            'diploma' => 'bi-mortarboard',
            'certificate' => 'bi-patch-check',
            'id_document' => 'bi-person-badge',
            'medical' => 'bi-heart-pulse',
            'other' => 'bi-file-earmark',
            default => 'bi-file-earmark',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Ожидает',
            'processing' => 'Обработка',
            'parsed' => 'Обработан',
            'failed' => 'Ошибка',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'parsed' => 'success',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' МБ';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' КБ';
        return $bytes . ' Б';
    }

    public function getIsParsedAttribute(): bool
    {
        return $this->status === self::STATUS_PARSED;
    }

    public function getHasAnalysisAttribute(): bool
    {
        return !empty($this->analysis_result);
    }

    // ========== Helpers ==========

    public function markAsProcessing(): bool
    {
        return $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsParsed(string $text, ?array $analysisResult = null): bool
    {
        return $this->update([
            'parsed_text' => $text,
            'status' => self::STATUS_PARSED,
            'analysis_result' => $analysisResult,
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $error): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
            'processed_at' => now(),
        ]);
    }

    public function getContents(): ?string
    {
        $fullPath = $this->getFullPath();
        return file_exists($fullPath) ? file_get_contents($fullPath) : null;
    }

    public function getBase64Contents(): ?string
    {
        $contents = $this->getContents();
        return $contents ? base64_encode($contents) : null;
    }

    public function getFullPath(): string
    {
        if (str_starts_with($this->path, 'public/')) {
            return storage_path('app/' . $this->path);
        }
        return storage_path('app/public/' . $this->path);
    }

    public static function getAllowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'txt', 'rtf', 'jpg', 'jpeg', 'png'];
    }

    public static function getMaxSizeBytes(): int
    {
        return 10 * 1024 * 1024;
    }
}
