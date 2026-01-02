<?php

namespace App\Helpers;

class DataAnonymizationHelper
{
    /**
     * Anonimiza um email removendo o domínio e substituindo por um genérico.
     */
    public static function anonymizeEmail(string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'anonymous@example.com';
        }

        return 'user' . substr(md5($email), 0, 8) . '@anonymous.local';
    }

    /**
     * Anonimiza um telefone substituindo por um número genérico.
     */
    public static function anonymizePhone(string $phone): string
    {
        if (empty($phone)) {
            return '0000-0000';
        }

        return '0000-0000';
    }

    /**
     * Anonimiza um nome substituindo por um nome genérico.
     */
    public static function anonymizeName(string $name): string
    {
        if (empty($name)) {
            return 'Anonymous User';
        }

        // Usa hash para gerar um nome "consistente" mas anônimo
        $hash = substr(md5($name), 0, 8);
        return 'User ' . strtoupper($hash);
    }

    /**
     * Anonimiza um CPF substituindo por um valor genérico.
     */
    public static function anonymizeCpf(string $cpf): string
    {
        return '000.000.000-00';
    }

    /**
     * Anonimiza um IP substituindo por um IP genérico.
     */
    public static function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return '::1';
        }
        
        return '0.0.0.0';
    }

    /**
     * Anonimiza dados de um array para uso em relatórios.
     */
    public static function anonymizeDataForReport(array $data, array $fieldsToAnonymize = null): array
    {
        $defaultFields = [
            'email' => 'anonymizeEmail',
            'telefone' => 'anonymizePhone',
            'phone' => 'anonymizePhone',
            'cpf' => 'anonymizeCpf',
            'nome' => 'anonymizeName',
            'name' => 'anonymizeName',
            'ip_address' => 'anonymizeIp',
            'ip' => 'anonymizeIp',
        ];
        
        $fields = $fieldsToAnonymize ?? $defaultFields;
        $anonymized = [];
        
        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);
            
            if (isset($fields[$lowerKey])) {
                $anonFunction = $fields[$lowerKey];
                
                if (is_callable($anonFunction)) {
                    $anonymized[$key] = $anonFunction($value);
                } elseif (method_exists(self::class, $anonFunction)) {
                    $anonymized[$key] = self::$anonFunction($value ?? '');
                } else {
                    $anonymized[$key] = 'ANONYMIZED';
                }
            } elseif (is_array($value)) {
                $anonymized[$key] = self::anonymizeDataForReport($value, $fields);
            } else {
                $anonymized[$key] = $value;
            }
        }
        
        return $anonymized;
    }

    /**
     * Anonimiza um registro de paciente mantendo apenas dados estatísticos.
     */
    public static function anonymizePatientData(array $patientData): array
    {
        return [
            'id' => null,
            'nome' => self::anonymizeName($patientData['nome'] ?? ''),
            'telefone' => self::anonymizePhone($patientData['telefone'] ?? ''),
            'email' => self::anonymizeEmail($patientData['email'] ?? ''),
            'sexo' => $patientData['sexo'] ?? null, // Mantém para análise estatística
            'idade' => $patientData['idade'] ?? null, // Mantém para análise estatística
            'tipo_paciente' => $patientData['tipo_paciente'] ?? null,
            'leito' => null,
            'setor' => $patientData['setor'] ?? null, // Pode manter para análise por setor
            'renda' => null,
            'tp_cod_convenio' => null,
        ];
    }

    /**
     * Anonimiza dados de usuário mantendo apenas informações não pessoais.
     */
    public static function anonymizeUserData(array $userData): array
    {
        return [
            'id' => null,
            'name' => self::anonymizeName($userData['name'] ?? ''),
            'email' => self::anonymizeEmail($userData['email'] ?? ''),
            'status' => $userData['status'] ?? null,
            'created_at' => $userData['created_at'] ?? null, // Mantém data para análise temporal
            'updated_at' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
        ];
    }
}
