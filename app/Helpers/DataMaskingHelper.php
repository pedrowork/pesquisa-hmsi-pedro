<?php

namespace App\Helpers;

class DataMaskingHelper
{
    /**
     * Mascara um email (ex: jo***@example.com).
     */
    public static function maskEmail(string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '***';
        }

        [$localPart, $domain] = explode('@', $email);
        
        if (strlen($localPart) <= 2) {
            $maskedLocal = str_repeat('*', strlen($localPart));
        } else {
            $maskedLocal = substr($localPart, 0, 2) . str_repeat('*', max(3, strlen($localPart) - 2));
        }
        
        return $maskedLocal . '@' . $domain;
    }

    /**
     * Mascara um telefone (ex: (11) 9****-1234).
     */
    public static function maskPhone(string $phone): string
    {
        if (empty($phone)) {
            return '***';
        }

        // Remove caracteres não numéricos
        $numbers = preg_replace('/\D/', '', $phone);
        
        if (strlen($numbers) <= 4) {
            return str_repeat('*', strlen($numbers));
        }
        
        // Mantém os últimos 4 dígitos visíveis
        $lastFour = substr($numbers, -4);
        $masked = str_repeat('*', strlen($numbers) - 4) . $lastFour;
        
        // Tenta manter formato original se possível
        if (strlen($numbers) === 11 && strpos($phone, '(') !== false) {
            return '(' . substr($masked, 0, 2) . ') ' . substr($masked, 2, 1) . '****-' . $lastFour;
        } elseif (strlen($numbers) === 10 && strpos($phone, '(') !== false) {
            return '(' . substr($masked, 0, 2) . ') ****-' . $lastFour;
        }
        
        return $masked;
    }

    /**
     * Mascara um CPF (ex: 123.***.***-45).
     */
    public static function maskCpf(string $cpf): string
    {
        if (empty($cpf)) {
            return '***';
        }

        $numbers = preg_replace('/\D/', '', $cpf);
        
        if (strlen($numbers) !== 11) {
            return str_repeat('*', min(strlen($cpf), 11));
        }
        
        return substr($numbers, 0, 3) . '.***.***-' . substr($numbers, -2);
    }

    /**
     * Mascara um nome (ex: João S***).
     */
    public static function maskName(string $name): string
    {
        if (empty($name)) {
            return '***';
        }

        $parts = explode(' ', trim($name));
        
        if (count($parts) === 1) {
            if (strlen($name) <= 3) {
                return str_repeat('*', strlen($name));
            }
            return substr($name, 0, 3) . str_repeat('*', strlen($name) - 3);
        }
        
        // Primeiro nome completo, último nome mascarado
        $firstName = array_shift($parts);
        $lastName = end($parts);
        
        $maskedLastName = strlen($lastName) > 1 
            ? substr($lastName, 0, 1) . str_repeat('*', strlen($lastName) - 1)
            : str_repeat('*', strlen($lastName));
        
        return $firstName . ' ' . $maskedLastName;
    }

    /**
     * Mascara um IP (ex: 192.168.***.***).
     */
    public static function maskIp(string $ip): string
    {
        if (empty($ip)) {
            return '***';
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return '***';
        }
        
        // IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.***.***';
        }
        
        // IPv6 - mascarar últimos 4 grupos
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            $masked = array_slice($parts, 0, -4);
            return implode(':', $masked) . ':***:***:***:***';
        }
        
        return '***';
    }

    /**
     * Mascara dados sensíveis em um array baseado em chaves conhecidas.
     */
    public static function maskSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'email' => 'maskEmail',
            'telefone' => 'maskPhone',
            'phone' => 'maskPhone',
            'cpf' => 'maskCpf',
            'nome' => 'maskName',
            'name' => 'maskName',
            'ip_address' => 'maskIp',
            'ip' => 'maskIp',
            'password' => fn($v) => '***',
            'two_factor_secret' => fn($v) => '***',
            'two_factor_recovery_codes' => fn($v) => '***',
            'remember_token' => fn($v) => '***',
        ];
        
        $masked = [];
        
        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);
            
            if (isset($sensitiveFields[$lowerKey])) {
                $maskFunction = $sensitiveFields[$lowerKey];
                
                if (is_callable($maskFunction)) {
                    $masked[$key] = $maskFunction($value);
                } elseif (method_exists(self::class, $maskFunction)) {
                    $masked[$key] = self::$maskFunction($value);
                } else {
                    $masked[$key] = '***';
                }
            } elseif (is_array($value)) {
                $masked[$key] = self::maskSensitiveData($value);
            } else {
                $masked[$key] = $value;
            }
        }
        
        return $masked;
    }

    /**
     * Mascara um valor genérico mantendo apenas primeiros e últimos caracteres.
     */
    public static function maskGeneric(string $value, int $visibleStart = 2, int $visibleEnd = 2): string
    {
        if (empty($value)) {
            return '***';
        }
        
        $length = strlen($value);
        
        if ($length <= ($visibleStart + $visibleEnd)) {
            return str_repeat('*', $length);
        }
        
        $start = substr($value, 0, $visibleStart);
        $end = substr($value, -$visibleEnd);
        $middle = str_repeat('*', $length - $visibleStart - $visibleEnd);
        
        return $start . $middle . $end;
    }
}
