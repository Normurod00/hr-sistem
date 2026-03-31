<?php

namespace App\Services;

/**
 * Санитизация пользовательского ввода для защиты от инъекций.
 * Используется во всех AI-related контроллерах и формах.
 */
class InputSanitizer
{
    /**
     * Очистить текстовое сообщение (чат, комментарии)
     */
    public static function sanitizeMessage(string $text): string
    {
        // Удаляем null bytes
        $text = str_replace("\0", '', $text);

        // Удаляем HTML теги
        $text = strip_tags($text);

        // Удаляем управляющие символы (кроме \n, \r, \t)
        $text = preg_replace('/[\x01-\x08\x0b\x0c\x0e-\x1f\x7f]/', '', $text);

        // Удаляем попытки SQL инъекций
        $text = preg_replace('/(\bunion\b\s+\bselect\b|\bdrop\b\s+\btable\b|\binsert\b\s+\binto\b|\bdelete\b\s+\bfrom\b)/i', '', $text);

        // Удаляем PHP код
        $text = preg_replace('/<\?php/i', '', $text);

        // Удаляем JavaScript
        $text = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $text);
        $text = preg_replace('/javascript:/i', '', $text);

        return trim($text);
    }

    /**
     * Очистить имя файла
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Берём только базовое имя (защита от path traversal)
        $filename = basename($filename);

        // Удаляем опасные символы
        $filename = preg_replace('/[^\w\s\-\.\(\)]/', '_', $filename);

        // Удаляем множественные точки (file...exe → file.exe)
        $filename = preg_replace('/\.{2,}/', '.', $filename);

        return $filename;
    }

    /**
     * Проверить что расширение файла безопасно для загрузки резюме
     */
    public static function isAllowedResumeExtension(string $filename): bool
    {
        $allowed = ['pdf', 'docx', 'doc', 'txt', 'rtf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($ext, $allowed, true);
    }

    /**
     * Проверить MIME-тип файла
     */
    public static function isAllowedMimeType(string $mimeType): bool
    {
        $allowed = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'text/rtf',
            'application/rtf',
            'image/jpeg',
            'image/png',
        ];

        return in_array($mimeType, $allowed, true);
    }
}
