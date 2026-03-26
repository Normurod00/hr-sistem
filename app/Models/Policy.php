<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category',
        'code',
        'title',
        'summary',
        'content',
        'file_path',
        'is_active',
        'version',
        'effective_date',
        'expiry_date',
        'created_by',
        'updated_by',
        'tags',
        'view_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'tags' => 'array',
    ];

    // ===== RELATIONSHIPS =====

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, string $term)
    {
        $term = '%' . $term . '%';
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', $term)
                ->orWhere('content', 'like', $term)
                ->orWhere('summary', 'like', $term)
                ->orWhere('code', 'like', $term);
        });
    }

    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    // ===== ACCESSORS =====

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'hr' => 'HR / Кадры',
            'finance' => 'Финансы',
            'security' => 'Безопасность',
            'operations' => 'Операции',
            'it' => 'IT',
            'compliance' => 'Комплаенс',
            'general' => 'Общее',
            default => $this->category,
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsEffectiveAttribute(): bool
    {
        return $this->is_active
            && $this->effective_date->isPast()
            && !$this->is_expired;
    }

    public function getExcerptAttribute(): string
    {
        return $this->summary ?: \Str::limit(strip_tags($this->content), 200);
    }

    // ===== HELPERS =====

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function getFileUrl(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }
        return \Storage::url($this->file_path);
    }

    public static function getCategories(): array
    {
        return [
            'hr' => 'HR / Кадры',
            'finance' => 'Финансы',
            'security' => 'Безопасность',
            'operations' => 'Операции',
            'it' => 'IT',
            'compliance' => 'Комплаенс',
            'general' => 'Общее',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => strip_tags($this->content),
            'category' => $this->category,
            'tags' => $this->tags,
        ];
    }
}
