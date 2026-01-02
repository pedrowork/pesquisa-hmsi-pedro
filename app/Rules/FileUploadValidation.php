<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class FileUploadValidation implements ValidationRule
{
    protected array $allowedMimeTypes;
    protected array $allowedExtensions;
    protected int $maxSize; // em KB
    protected bool $scanForVirus;

    public function __construct(
        array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
        int $maxSize = 5120, // 5MB padrão
        bool $scanForVirus = false
    ) {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->allowedExtensions = $allowedExtensions;
        $this->maxSize = $maxSize;
        $this->scanForVirus = $scanForVirus;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, \Illuminate\Translation\PotentiallyTranslatedString): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('O arquivo fornecido não é válido.');
            return;
        }

        // Verificar tamanho do arquivo
        $fileSizeInKB = $value->getSize() / 1024;
        if ($fileSizeInKB > $this->maxSize) {
            $fail("O arquivo :attribute não pode ser maior que {$this->maxSize}KB.");
            return;
        }

        // Verificar extensão
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            $fail("O arquivo :attribute deve ter uma das seguintes extensões: " . implode(', ', $this->allowedExtensions) . ".");
            return;
        }

        // Verificar MIME type real (não apenas o informado)
        $mimeType = $value->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $fail("O tipo de arquivo :attribute não é permitido.");
            return;
        }

        // Verificar se a extensão corresponde ao MIME type
        $expectedMimeTypes = $this->getExpectedMimeTypes($extension);
        if (!in_array($mimeType, $expectedMimeTypes)) {
            $fail("O arquivo :attribute não corresponde ao tipo esperado para a extensão {$extension}.");
            return;
        }

        // Verificar arquivo malicioso (tentativa básica)
        if ($this->isPotentiallyMalicious($value)) {
            $fail("O arquivo :attribute parece ser malicioso e foi rejeitado.");
            return;
        }

        // Verificar se é um arquivo de imagem válido
        if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            $imageInfo = @getimagesize($value->getRealPath());
            if ($imageInfo === false) {
                $fail("O arquivo :attribute não é uma imagem válida.");
                return;
            }
        }
    }

    /**
     * Retorna os MIME types esperados para uma extensão.
     */
    protected function getExpectedMimeTypes(string $extension): array
    {
        $mimeMap = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'txt' => ['text/plain'],
        ];

        return $mimeMap[$extension] ?? [];
    }

    /**
     * Verifica se o arquivo pode ser potencialmente malicioso.
     */
    protected function isPotentiallyMalicious(UploadedFile $file): bool
    {
        $filename = $file->getClientOriginalName();
        
        // Verificar extensões perigosas disfarçadas
        $dangerousPatterns = [
            '/\.php\.(jpg|png|gif)$/i',
            '/\.(exe|bat|cmd|com|pif|scr|vbs|js|jar)$/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        // Verificar se o nome do arquivo contém caracteres suspeitos
        if (preg_match('/[<>:"|?*\\\\]/', $filename)) {
            return true;
        }

        // Verificar conteúdo suspeito (primeiros bytes)
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle) {
            $header = fread($handle, 512);
            fclose($handle);

            // Verificar assinaturas de arquivos executáveis
            $executableSignatures = [
                'MZ', // PE executável (Windows)
                "\x7FELF", // ELF executável (Linux)
                "\xCA\xFE\xBA\xBE", // Java class file
            ];

            foreach ($executableSignatures as $signature) {
                if (str_starts_with($header, $signature)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Métodos estáticos para configurações comuns.
     */
    public static function image(): self
    {
        return new self(
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            5120 // 5MB
        );
    }

    public static function document(): self
    {
        return new self(
            ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['pdf', 'doc', 'docx'],
            10240 // 10MB
        );
    }

    public static function spreadsheet(): self
    {
        return new self(
            ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ['xls', 'xlsx'],
            10240 // 10MB
        );
    }
}
