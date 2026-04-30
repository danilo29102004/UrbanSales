<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ZapatillaUploadService
{
    private string $uploadDir;

    public function __construct(string $projectDir)
    {
        $this->uploadDir = $projectDir . '/public/uploads/zapatillas';
    }

    public function uploadImage(UploadedFile $file): string
    {
        // Crear el directorio si no existe
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Generar nombre único para la imagen
        $filename = uniqid('zapatilla_') . '.' . $file->guessExtension();

        // Mover el archivo
        $file->move($this->uploadDir, $filename);

        // Retornar la ruta relativa para guardar en BD
        return '/uploads/zapatillas/' . $filename;
    }

    public function deleteImage(string $imagePath): bool
    {
        if (!$imagePath) {
            return false;
        }

        $filePath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
