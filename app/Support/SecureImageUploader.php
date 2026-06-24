<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class SecureImageUploader
{
    /**
     * @return array<int, string>
     */
    public function validationRules(): array
    {
        return [
            'nullable',
            'file',
            'mimes:jpg,jpeg,png,webp',
            'max:'.config('image_uploads.max_kilobytes', 10240),
            'dimensions:max_width='.config('image_uploads.max_width', 6000).',max_height='.config('image_uploads.max_height', 6000),
        ];
    }

    public function effectiveMaxKilobytes(): int
    {
        $applicationLimit = (int) config('image_uploads.max_kilobytes', 10240);
        $phpLimit = $this->iniKilobytes((string) ini_get('upload_max_filesize'));

        return $phpLimit > 0 ? min($applicationLimit, $phpLimit) : $applicationLimit;
    }

    public function effectiveMaxMegabytes(): string
    {
        return number_format($this->effectiveMaxKilobytes() / 1024, 0);
    }

    public function upload(UploadedFile $file, string $directory, string $errorKey = 'image_upload'): string
    {
        $this->assertValidImage($file, $errorKey);
        $sourcePath = $file->getRealPath();

        if (! is_string($sourcePath) || $sourcePath === '') {
            $this->fail($errorKey, 'No se pudo acceder al archivo de imagen.');
        }

        $temporaryOutput = tempnam(sys_get_temp_dir(), 'safe-image-');

        if ($temporaryOutput === false) {
            throw new RuntimeException('No se pudo preparar el archivo temporal de imagen.');
        }

        $temporaryWebp = $temporaryOutput.'.webp';

        try {
            $this->reencodeAsWebp($sourcePath, $temporaryWebp, $errorKey);
            $this->assertReencodedImage($temporaryWebp);

            $path = trim($directory, '/').'/'.now()->format('Y/m').'/'.Str::uuid().'.webp';
            $stream = fopen($temporaryWebp, 'rb');

            if ($stream === false) {
                throw new RuntimeException('No se pudo leer la imagen procesada.');
            }

            try {
                $stored = Storage::disk($this->disk())->put($path, $stream, [
                    'visibility' => 'public',
                    'ContentType' => 'image/webp',
                ]);
            } finally {
                fclose($stream);
            }

            if (! $stored) {
                throw new RuntimeException('No se pudo guardar la imagen.');
            }

            return Storage::disk($this->disk())->url($path);
        } finally {
            @unlink($temporaryOutput);
            @unlink($temporaryWebp);
        }
    }

    /**
     * @param array<int, UploadedFile> $files
     * @return array<int, string>
     */
    public function uploadMany(array $files, string $directory, string $errorKey = 'image_upload'): array
    {
        $uploadedUrls = [];

        try {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadedUrls[] = $this->upload($file, $directory, $errorKey);
                }
            }
        } catch (Throwable $exception) {
            foreach ($uploadedUrls as $url) {
                $this->deleteManagedUrl($url);
            }

            throw $exception;
        }

        return $uploadedUrls;
    }

    public function deleteManagedUrl(?string $url): void
    {
        if (! filled($url)) {
            return;
        }

        $disk = Storage::disk($this->disk());
        $baseUrl = rtrim($disk->url(''), '/').'/';

        if (! str_starts_with($url, $baseUrl)) {
            return;
        }

        $path = ltrim(substr($url, strlen($baseUrl)), '/');

        if ($path !== '' && str_ends_with(mb_strtolower($path), '.webp')) {
            $disk->delete($path);
        }
    }

    private function assertValidImage(UploadedFile $file, string $errorKey): void
    {
        if (! $file->isValid()) {
            $this->fail($errorKey, 'La carga de la imagen no se completó correctamente.');
        }

        $maxBytes = ((int) config('image_uploads.max_kilobytes', 10240)) * 1024;

        if ($file->getSize() === false || $file->getSize() <= 0 || $file->getSize() > $maxBytes) {
            $this->fail($errorKey, 'La imagen supera el tamaño permitido.');
        }

        $allowedMimes = config('image_uploads.allowed_mimes', []);
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        $extension = mb_strtolower($file->getClientOriginalExtension());

        if (! is_string($detectedMime) || ! isset($allowedMimes[$detectedMime])) {
            $this->fail($errorKey, 'El archivo debe ser una imagen JPEG, PNG o WebP real.');
        }

        if (! in_array($extension, $allowedMimes[$detectedMime], true)) {
            $this->fail($errorKey, 'La extensión del archivo no coincide con el formato real de la imagen.');
        }

        $imageInfo = @getimagesize($file->getRealPath());

        if ($imageInfo === false || empty($imageInfo[0]) || empty($imageInfo[1])) {
            $this->fail($errorKey, 'No se pudo interpretar el contenido de la imagen.');
        }

        [$width, $height] = $imageInfo;
        $maxWidth = (int) config('image_uploads.max_width', 6000);
        $maxHeight = (int) config('image_uploads.max_height', 6000);
        $maxPixels = (int) config('image_uploads.max_pixels', 25000000);

        if ($width > $maxWidth || $height > $maxHeight || ($width * $height) > $maxPixels) {
            $this->fail($errorKey, 'Las dimensiones de la imagen superan el límite permitido.');
        }
    }

    private function reencodeAsWebp(string $source, string $destination, string $errorKey): void
    {
        if (class_exists(\Imagick::class)) {
            $this->reencodeWithImagick($source, $destination);

            return;
        }

        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $this->reencodeWithGd($source, $destination, $errorKey);

            return;
        }

        $this->reencodeWithImageMagick($source, $destination, $errorKey);
    }

    private function reencodeWithImagick(string $source, string $destination): void
    {
        $image = new \Imagick($source.'[0]');
        $image->autoOrient();
        $image->stripImage();
        [$targetWidth, $targetHeight] = $this->targetDimensions(
            $image->getImageWidth(),
            $image->getImageHeight(),
        );

        if ($targetWidth !== $image->getImageWidth() || $targetHeight !== $image->getImageHeight()) {
            $image->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_LANCZOS, 1);
        }

        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($this->quality());

        if (! $image->writeImage($destination)) {
            throw new RuntimeException('No se pudo procesar la imagen.');
        }

        $image->clear();
        $image->destroy();
    }

    private function reencodeWithGd(string $source, string $destination, string $errorKey): void
    {
        $contents = file_get_contents($source);
        $image = $contents !== false ? @imagecreatefromstring($contents) : false;

        if ($image === false) {
            $this->fail($errorKey, 'No se pudo decodificar la imagen.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        [$targetWidth, $targetHeight] = $this->targetDimensions($width, $height);
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        if (! imagewebp($target, $destination, $this->quality())) {
            imagedestroy($image);
            imagedestroy($target);
            throw new RuntimeException('No se pudo procesar la imagen.');
        }

        imagedestroy($image);
        imagedestroy($target);
    }

    private function reencodeWithImageMagick(string $source, string $destination, string $errorKey): void
    {
        $binary = $this->imageMagickBinary();

        if ($binary === null) {
            throw new RuntimeException('El servidor necesita GD, Imagick o ImageMagick para procesar imágenes de forma segura.');
        }

        $process = new Process([
            $binary,
            '-limit', 'memory', '256MiB',
            '-limit', 'map', '512MiB',
            '-limit', 'disk', '1GiB',
            $source.'[0]',
            '-auto-orient',
            '-strip',
            '-thumbnail', $this->outputMaxWidth().'x'.$this->outputMaxHeight().'>',
            '-quality', (string) $this->quality(),
            'webp:'.$destination,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->fail($errorKey, 'La imagen no pudo procesarse de forma segura.');
        }
    }

    private function assertReencodedImage(string $path): void
    {
        $mime = is_file($path) ? (new \finfo(FILEINFO_MIME_TYPE))->file($path) : false;
        $imageInfo = is_file($path) ? @getimagesize($path) : false;

        if ($mime !== 'image/webp' || $imageInfo === false) {
            throw new RuntimeException('La imagen procesada no es válida.');
        }
    }

    private function imageMagickBinary(): ?string
    {
        return (new ExecutableFinder())->find('magick');
    }

    private function disk(): string
    {
        return (string) config('image_uploads.disk', 'public');
    }

    private function quality(): int
    {
        return max(1, min(100, (int) config('image_uploads.webp_quality', 85)));
    }

    private function outputMaxWidth(): int
    {
        return max(1, (int) config('image_uploads.output_max_width', 2400));
    }

    private function outputMaxHeight(): int
    {
        return max(1, (int) config('image_uploads.output_max_height', 2400));
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function targetDimensions(int $width, int $height): array
    {
        $scale = min(
            1,
            $this->outputMaxWidth() / max(1, $width),
            $this->outputMaxHeight() / max(1, $height),
        );

        return [
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
        ];
    }

    private function iniKilobytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $number = (float) $value;

        return match (mb_strtolower(substr($value, -1))) {
            'g' => (int) ($number * 1024 * 1024),
            'm' => (int) ($number * 1024),
            'k' => (int) $number,
            default => (int) ceil($number / 1024),
        };
    }

    private function fail(string $errorKey, string $message): never
    {
        throw ValidationException::withMessages([
            $errorKey => $message,
        ]);
    }
}
