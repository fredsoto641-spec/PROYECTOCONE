<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DiagnoseImageUploads
{
    private const IMAGE_FIELDS = [
        'image_file',
        'cover_image_file',
        'gallery_image_files',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->requestExceedsPostLimit($request)) {
            $details = [
                'summary' => 'La solicitud completa supera el límite permitido por el servidor.',
                'suggestion' => 'Reduce el número o tamaño de las imágenes, o aumenta post_max_size en PHP.',
                'technical' => $this->technicalContext($request, null, null, 'CONTENT_LENGTH supera post_max_size'),
            ];

            return back()
                ->withInput()
                ->withErrors(['image_upload' => $details['summary']])
                ->with('upload_error_modal', $details);
        }

        foreach ($this->uploadedImages($request) as [$field, $file]) {
            if ($file->getError() === UPLOAD_ERR_OK || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $details = $this->detailsForUploadError($request, $field, $file);

            return back()
                ->withInput()
                ->withErrors([$field => $details['summary']])
                ->with('upload_error_modal', $details);
        }

        try {
            return $next($request);
        } catch (ValidationException $exception) {
            if ($this->containsImageValidationError($exception->errors())) {
                session()->flash('upload_error_modal', $this->detailsForValidationError($request, $exception));
            }

            throw $exception;
        }
    }

    /**
     * @return array<int, array{0: string, 1: UploadedFile}>
     */
    private function uploadedImages(Request $request): array
    {
        $files = [];

        foreach (self::IMAGE_FIELDS as $field) {
            $value = $request->file($field);

            foreach ($this->flattenFiles($value) as $file) {
                $files[] = [$field, $file];
            }
        }

        return $files;
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function flattenFiles(mixed $value): array
    {
        if ($value instanceof UploadedFile) {
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->flatMap(fn ($file) => $this->flattenFiles($file))
            ->values()
            ->all();
    }

    private function requestExceedsPostLimit(Request $request): bool
    {
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postLimit = $this->iniBytes((string) ini_get('post_max_size'));

        return $contentLength > 0 && $postLimit > 0 && $contentLength > $postLimit;
    }

    /**
     * @return array{summary: string, suggestion: string, technical: array<string, int|string|null>}
     */
    private function detailsForUploadError(Request $request, string $field, UploadedFile $file): array
    {
        [$summary, $suggestion] = match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE => [
                'La imagen supera el tamaño máximo aceptado por PHP.',
                'Usa una imagen menor de '.ini_get('upload_max_filesize').' o aumenta upload_max_filesize y post_max_size.',
            ],
            UPLOAD_ERR_FORM_SIZE => [
                'La imagen supera el tamaño permitido por el formulario.',
                'Reduce el tamaño del archivo e inténtalo nuevamente.',
            ],
            UPLOAD_ERR_PARTIAL => [
                'La imagen se recibió de forma incompleta.',
                'Comprueba tu conexión y vuelve a seleccionar el archivo.',
            ],
            UPLOAD_ERR_NO_TMP_DIR => [
                'El servidor no tiene disponible su directorio temporal de cargas.',
                'El administrador debe revisar upload_tmp_dir y los permisos del sistema.',
            ],
            UPLOAD_ERR_CANT_WRITE => [
                'El servidor no pudo escribir temporalmente la imagen.',
                'El administrador debe revisar espacio en disco y permisos.',
            ],
            UPLOAD_ERR_EXTENSION => [
                'Una extensión de PHP detuvo la carga de la imagen.',
                'El administrador debe revisar la configuración y extensiones de PHP.',
            ],
            default => [
                'No fue posible recibir la imagen.',
                'Vuelve a intentarlo con otra imagen o consulta al administrador.',
            ],
        };

        return [
            'summary' => $summary,
            'suggestion' => $suggestion,
            'technical' => $this->technicalContext(
                $request,
                $field,
                $file,
                $file->getErrorMessage(),
            ),
        ];
    }

    /**
     * @return array{summary: string, suggestion: string, technical: array<string, int|string|null>}
     */
    private function detailsForValidationError(Request $request, ValidationException $exception): array
    {
        $errors = collect($exception->errors())
            ->filter(fn ($messages, $field): bool => $this->isImageField((string) $field))
            ->flatten()
            ->values();
        $field = collect(array_keys($exception->errors()))
            ->first(fn (string $field): bool => $this->isImageField($field));
        $file = $field ? $this->flattenFiles($request->file(strtok($field, '.')))[0] ?? null : null;
        $technicalReason = $errors->implode(' | ');
        $normalizedSummary = match (true) {
            str_contains(mb_strtolower($technicalReason), 'failed to upload') =>
                'PHP no pudo completar la carga de la imagen.',
            str_contains(mb_strtolower($technicalReason), 'must be a file of type'),
            str_contains(mb_strtolower($technicalReason), 'debe ser un archivo de tipo') =>
                'El formato de la imagen no está permitido.',
            str_contains(mb_strtolower($technicalReason), 'dimensions'),
            str_contains(mb_strtolower($technicalReason), 'dimensiones') =>
                'Las dimensiones de la imagen superan el límite permitido.',
            str_contains(mb_strtolower($technicalReason), 'kilobytes'),
            str_contains(mb_strtolower($technicalReason), 'greater than') =>
                'La imagen supera el tamaño permitido por la aplicación.',
            default => 'La imagen no cumple los requisitos de carga.',
        };

        return [
            'summary' => $normalizedSummary,
            'suggestion' => 'Usa un archivo JPEG, PNG o WebP real, dentro de los límites indicados.',
            'technical' => $this->technicalContext(
                $request,
                $field,
                $file,
                $technicalReason,
            ),
        ];
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function containsImageValidationError(array $errors): bool
    {
        return collect(array_keys($errors))->contains(
            fn (string $field): bool => $this->isImageField($field),
        );
    }

    private function isImageField(string $field): bool
    {
        return collect(self::IMAGE_FIELDS)
            ->contains(fn (string $imageField): bool => $field === $imageField || str_starts_with($field, $imageField.'.'))
            || $field === 'image_upload';
    }

    /**
     * @return array<string, int|string|null>
     */
    private function technicalContext(
        Request $request,
        ?string $field,
        ?UploadedFile $file,
        ?string $reason,
    ): array {
        return [
            'campo' => $field,
            'archivo' => $file?->getClientOriginalName(),
            'codigo_php' => $file?->getError(),
            'detalle_php' => $reason,
            'tamaño_recibido_bytes' => $file?->getSize(),
            'content_length_bytes' => (int) $request->server('CONTENT_LENGTH', 0),
            'upload_max_filesize' => (string) ini_get('upload_max_filesize'),
            'post_max_size' => (string) ini_get('post_max_size'),
            'max_file_uploads' => (string) ini_get('max_file_uploads'),
            'limite_aplicacion_kb' => (int) config('image_uploads.max_kilobytes', 10240),
            'php_sapi' => PHP_SAPI,
        ];
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $number = (float) $value;

        return match (mb_strtolower(substr($value, -1))) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
