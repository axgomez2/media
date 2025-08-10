<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class NewsImageValidation implements ValidationRule
{
    protected string $type;
    protected array $allowedMimes = ['jpeg', 'jpg', 'png', 'webp'];
    protected int $maxSize = 2048; // 2MB in KB

    public function __construct(string $type = 'general')
    {
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('O arquivo deve ser uma imagem válida.');
            return;
        }

        // Check if file is actually an image
        if (!$value->isValid()) {
            $fail('O arquivo de imagem está corrompido ou inválido.');
            return;
        }

        // Check MIME type
        if (!in_array($value->getClientOriginalExtension(), $this->allowedMimes)) {
            $fail('A imagem deve ser do tipo: ' . implode(', ', $this->allowedMimes) . '.');
            return;
        }

        // Check file size
        if ($value->getSize() > $this->maxSize * 1024) {
            $fail('A imagem não pode exceder ' . ($this->maxSize / 1024) . 'MB.');
            return;
        }

        // Get image dimensions
        $imageInfo = getimagesize($value->getPathname());
        if (!$imageInfo) {
            $fail('Não foi possível determinar as dimensões da imagem.');
            return;
        }

        [$width, $height] = $imageInfo;

        // Type-specific validations
        switch ($this->type) {
            case 'featured':
                if ($width < 300 || $height < 200) {
                    $fail('A imagem de destaque deve ter pelo menos 300x200 pixels.');
                    return;
                }

                // Check aspect ratio for featured images (should be landscape-ish)
                $aspectRatio = $width / $height;
                if ($aspectRatio < 1.2) {
                    $fail('A imagem de destaque deve ter proporção mais próxima do formato paisagem (largura maior que altura).');
                    return;
                }
                break;

            case 'gallery':
                if ($width < 200 || $height < 150) {
                    $fail('As imagens da galeria devem ter pelo menos 200x150 pixels.');
                    return;
                }
                break;
        }

        // Check for potential security issues
        $this->validateImageSecurity($value, $fail);
    }

    /**
     * Validate image for security issues
     */
    protected function validateImageSecurity(UploadedFile $file, Closure $fail): void
    {
        // Check for executable extensions in filename
        $filename = $file->getClientOriginalName();
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi'];

        foreach ($dangerousExtensions as $ext) {
            if (str_contains(strtolower($filename), '.' . $ext)) {
                $fail('Nome do arquivo contém extensão não permitida.');
                return;
            }
        }

        // Basic check for PHP code in image files
        $content = file_get_contents($file->getPathname());
        if (str_contains($content, '<?php') || str_contains($content, '<?=')) {
            $fail('Arquivo de imagem contém código suspeito.');
            return;
        }

        // Check file signature matches extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $fail('Tipo de arquivo não corresponde ao conteúdo da imagem.');
            return;
        }
    }

    /**
     * Create instance for featured image validation
     */
    public static function featured(): self
    {
        return new self('featured');
    }

    /**
     * Create instance for gallery image validation
     */
    public static function gallery(): self
    {
        return new self('gallery');
    }
}
