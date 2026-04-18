<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ManuscriptStorage
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly string $manuscriptUploadDir,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        return $this->fileUploader->upload($file, $this->manuscriptUploadDir, 'manuscript');
    }

    public function resolvePath(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        return $this->manuscriptUploadDir . '/' . ltrim($filename, '/');
    }

    public function delete(?string $filename): void
    {
        $this->fileUploader->deleteIfExists($this->resolvePath($filename));
    }
}
