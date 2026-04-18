<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileUploader
{
    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    public function upload(UploadedFile $file, string $targetDirectory, string $prefix = 'file'): string
    {
        $this->filesystem->mkdir($targetDirectory);

        $safeName = (new AsciiSlugger())->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->lower()->toString();
        $safeName = $safeName !== '' ? $safeName : $prefix;
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('%s-%s.%s', $prefix . '-' . $safeName, bin2hex(random_bytes(6)), $extension);

        $file->move($targetDirectory, $filename);

        return $filename;
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path && $this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }
}
