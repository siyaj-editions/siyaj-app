<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookCoverStorage
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly string $projectDir,
        private readonly string $bookCoverUploadDir,
        private readonly string $bookCoverPublicPrefix,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $filename = $this->fileUploader->upload($file, $this->bookCoverUploadDir, 'book-cover');

        return rtrim($this->bookCoverPublicPrefix, '/') . '/' . $filename;
    }

    public function delete(?string $coverImagePath): void
    {
        if (!$coverImagePath || !str_starts_with($coverImagePath, rtrim($this->bookCoverPublicPrefix, '/') . '/')) {
            return;
        }

        $relativePath = ltrim(substr($coverImagePath, strlen(rtrim($this->bookCoverPublicPrefix, '/'))), '/');
        $absolutePath = $this->bookCoverUploadDir . '/' . $relativePath;
        $this->fileUploader->deleteIfExists($absolutePath);
    }
}
