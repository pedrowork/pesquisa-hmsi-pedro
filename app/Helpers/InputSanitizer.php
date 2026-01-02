<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class InputSanitizer
{
    /**
     * Remove tags HTML e PHP, escapando caracteres especiais.
     */
    public static function sanitizeString(string $input): string
    {
        // Remove tags HTML e PHP
        $sanitized = strip_tags($input);
        
        // Escapa caracteres especiais HTML
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8', false);
        
        // Remove caracteres de controle
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/', '', $sanitized);
        
        return trim($sanitized);
    }

    /**
     * Sanitiza um array de strings recursivamente.
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $sanitizedKey = self::sanitizeString($key);
            
            if (is_array($value)) {
                $sanitized[$sanitizedKey] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$sanitizedKey] = self::sanitizeString($value);
            } else {
                $sanitized[$sanitizedKey] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitiza dados de formulário, preservando campos específicos que podem conter HTML válido.
     */
    public static function sanitizeFormData(array $data, array $allowedHtmlFields = []): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedHtmlFields) && is_string($value)) {
                // Para campos que podem conter HTML, apenas remove tags perigosas
                $sanitized[$key] = self::sanitizeHtmlContent($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeFormData($value, $allowedHtmlFields);
            } elseif (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Remove apenas tags HTML perigosas, mantendo HTML seguro.
     */
    public static function sanitizeHtmlContent(string $html): string
    {
        // Lista de tags permitidas (configurável)
        $allowedTags = '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6>';
        
        // Remove tags não permitidas
        $sanitized = strip_tags($html, $allowedTags);
        
        // Remove atributos de tags (evita javascript: em href, etc)
        $sanitized = preg_replace('/<([^>]+?)(\s+on\w+\s*=\s*["\'][^"\']*["\'])([^>]*?)>/i', '<$1$3>', $sanitized);
        $sanitized = preg_replace('/<([^>]+?)(\s+on\w+\s*=\s*[^\s>]+)([^>]*?)>/i', '<$1$3>', $sanitized);
        
        return trim($sanitized);
    }

    /**
     * Valida e sanitiza um email.
     */
    public static function sanitizeEmail(string $email): ?string
    {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower($email);
        }
        
        return null;
    }

    /**
     * Valida e sanitiza uma URL.
     */
    public static function sanitizeUrl(string $url): ?string
    {
        $url = trim($url);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        return null;
    }

    /**
     * Sanitiza um número, removendo caracteres não numéricos.
     */
    public static function sanitizeNumeric(string $value): string
    {
        return preg_replace('/[^0-9.-]/', '', $value);
    }

    /**
     * Remove possíveis tentativas de SQL injection.
     */
    public static function sanitizeSql(string $input): string
    {
        // Remove palavras-chave SQL perigosas (básico - usar prepared statements é melhor)
        $dangerous = ['DROP', 'DELETE', 'INSERT', 'UPDATE', 'ALTER', 'CREATE', 'TRUNCATE', 'EXEC', 'EXECUTE'];
        $input = preg_replace('/\b(' . implode('|', $dangerous) . ')\b/i', '', $input);
        
        return trim($input);
    }

    /**
     * Remove caracteres não permitidos para nomes de arquivo.
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove caracteres perigosos
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove múltiplos underscores consecutivos
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Limita tamanho
        if (strlen($filename) > 255) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = substr(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - strlen($extension) - 1);
            $filename = $name . '.' . $extension;
        }
        
        return $filename;
    }
}
