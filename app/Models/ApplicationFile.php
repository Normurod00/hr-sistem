<?php

namespace App\Models;

use App\Enums\FileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApplicationFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'file_type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'parsed_text',
        'is_parsed',
    ];

    protected function casts(): array
    {
        return [
            'file_type' => FileType::class,
            'size' => 'integer',
            'is_parsed' => 'boolean',
        ];
    }

    // ========== Relationships ==========

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // ========== Scopes ==========

    public function scopeResumes($query)
    {
        return $query->where('file_type', FileType::Resume);
    }

    public function scopeUnparsed($query)
    {
        return $query->where('is_parsed', false);
    }

    public function scopeParsed($query)
    {
        return $query->where('is_parsed', true);
    }

    // ========== Accessors ==========

    public function getFileTypeLabelAttribute(): string
    {
        return $this->file_type?->label() ?? 'Файл';
    }

    public function getFileTypeIconAttribute(): string
    {
        return $this->file_type?->icon() ?? 'document';
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getFullPathAttribute(): string
    {
        return $this->getFullPath();
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size ?? 0;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' МБ';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' КБ';
        }

        return $bytes . ' Б';
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    public function getIsResumeAttribute(): bool
    {
        return $this->file_type === FileType::Resume;
    }

    public function getHasParsedTextAttribute(): bool
    {
        return !empty($this->parsed_text);
    }

    // ========== Helpers ==========

    public function markAsParsed(string $text): bool
    {
        return $this->update([
            'parsed_text' => $text,
            'is_parsed' => true,
        ]);
    }

    public function getContents(): ?string
    {
        $fullPath = $this->getFullPath();

        if (!file_exists($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath);
    }

    public function getBase64Contents(): ?string
    {
        $contents = $this->getContents();
        return $contents ? base64_encode($contents) : null;
    }

    public function getFullPath(): string
    {
        // Поддержка путей с 'public/' и без
        if (str_starts_with($this->path, 'public/')) {
            return storage_path('app/' . $this->path);
        }

        return storage_path('app/public/' . $this->path);
    }

    public function deleteFile(): bool
    {
        $fullPath = $this->getFullPath();

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    public static function getAllowedMimeTypes(): array
    {
        return [
            // Документы
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'application/rtf',
            // Изображения (для OCR)
            'image/jpeg',
            'image/png',
            'image/bmp',
            'image/tiff',
            'image/x-ms-bmp',
        ];
    }

    public static function getAllowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'txt', 'rtf', 'jpg', 'jpeg', 'png', 'bmp', 'tiff', 'tif'];
    }

    /**
     * Проверка, является ли файл изображением (для OCR)
     */
    public function getIsImageAttribute(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'bmp', 'tiff', 'tif'];
        return in_array(strtolower($this->extension), $imageExtensions);
    }

    public static function getMaxSizeBytes(): int
    {
        return 10 * 1024 * 1024; // 10 MB
    }
}
