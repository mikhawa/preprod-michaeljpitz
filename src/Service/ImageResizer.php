<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageResizer
{
    private const DEFAULT_MAX_WIDTH = 1200;
    private const DEFAULT_QUALITY = 90;

    /**
     * Redimensionne une image si elle dépasse la largeur maximale.
     * Corrige automatiquement l'orientation EXIF.
     * Retourne le chemin du fichier redimensionné.
     */
    public function resize(
        UploadedFile $file,
        string $destinationPath,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $quality = self::DEFAULT_QUALITY,
    ): string {
        $mimeType = $file->getMimeType();
        $sourcePath = $file->getPathname();

        // Obtenir les dimensions originales
        $imageInfo = getimagesize($sourcePath);
        if (false === $imageInfo) {
            $file->move(\dirname($destinationPath), basename($destinationPath));

            return $destinationPath;
        }

        // Créer l'image source selon le type MIME
        $sourceImage = $this->createImageFromFile($sourcePath, $mimeType);
        if (null === $sourceImage) {
            $file->move(\dirname($destinationPath), basename($destinationPath));

            return $destinationPath;
        }

        // Corriger l'orientation EXIF (surtout pour les photos de smartphones)
        $sourceImage = $this->fixExifOrientation($sourceImage, $sourcePath, $mimeType);

        // Obtenir les dimensions après correction d'orientation
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        // Déterminer si un redimensionnement est nécessaire
        $needsResize = $originalWidth > $maxWidth;

        if ($needsResize) {
            // Calculer les nouvelles dimensions
            $ratio = $maxWidth / $originalWidth;
            $newWidth = max(1, $maxWidth);
            $newHeight = max(1, (int) round($originalHeight * $ratio));

            // Créer l'image redimensionnée
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            if (false === $resizedImage) {
                imagedestroy($sourceImage);
                $file->move(\dirname($destinationPath), basename($destinationPath));

                return $destinationPath;
            }

            // Préserver la transparence
            $this->preserveTransparency($resizedImage, $sourceImage, $mimeType);

            // Redimensionner
            imagecopyresampled(
                $resizedImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            // Sauvegarder l'image redimensionnée
            $this->saveImage($resizedImage, $destinationPath, $mimeType, $quality);

            // Libérer la mémoire
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);
        } else {
            // Pas de redimensionnement mais on sauvegarde quand même
            // pour appliquer la correction d'orientation
            $this->saveImage($sourceImage, $destinationPath, $mimeType, $quality);
            imagedestroy($sourceImage);
        }

        return $destinationPath;
    }

    /**
     * Corrige l'orientation de l'image selon les données EXIF.
     */
    private function fixExifOrientation(\GdImage $image, string $path, ?string $mimeType): \GdImage
    {
        // EXIF n'est disponible que pour JPEG
        if ('image/jpeg' !== $mimeType || !\function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        if (false === $exif || !isset($exif['Orientation'])) {
            return $image;
        }

        $orientation = $exif['Orientation'];

        // Appliquer la rotation/flip selon l'orientation EXIF
        // https://exiftool.org/TagNames/EXIF.html (Orientation)
        $rotatedImage = match ($orientation) {
            2 => imageflip($image, IMG_FLIP_HORIZONTAL) ? $image : $image,
            3 => imagerotate($image, 180, 0),
            4 => imageflip($image, IMG_FLIP_VERTICAL) ? $image : $image,
            5 => $this->rotateAndFlip($image, 270, IMG_FLIP_HORIZONTAL),
            6 => imagerotate($image, -90, 0),
            7 => $this->rotateAndFlip($image, 90, IMG_FLIP_HORIZONTAL),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        // imagerotate retourne une nouvelle image ou false
        if (false === $rotatedImage) {
            return $image;
        }

        // Si une nouvelle image a été créée, détruire l'ancienne
        if ($rotatedImage !== $image) {
            imagedestroy($image);
        }

        return $rotatedImage;
    }

    /**
     * Applique une rotation puis un flip.
     */
    private function rotateAndFlip(\GdImage $image, int $angle, int $flipMode): \GdImage
    {
        $rotated = imagerotate($image, $angle, 0);
        if (false === $rotated) {
            return $image;
        }

        imageflip($rotated, $flipMode);

        return $rotated;
    }

    /**
     * Préserve la transparence selon le type d'image.
     */
    private function preserveTransparency(\GdImage $destImage, \GdImage $srcImage, ?string $mimeType): void
    {
        // Préserver la transparence pour PNG et WebP
        if (\in_array($mimeType, ['image/png', 'image/webp'], true)) {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
            if (false !== $transparent) {
                imagefill($destImage, 0, 0, $transparent);
            }
        }

        // Préserver la transparence pour GIF
        if ('image/gif' === $mimeType) {
            $transparentIndex = imagecolortransparent($srcImage);
            if ($transparentIndex >= 0) {
                $transparentColor = imagecolorsforindex($srcImage, $transparentIndex);
                $transparentNew = imagecolorallocate(
                    $destImage,
                    $transparentColor['red'],
                    $transparentColor['green'],
                    $transparentColor['blue']
                );
                if (false !== $transparentNew) {
                    imagefill($destImage, 0, 0, $transparentNew);
                    imagecolortransparent($destImage, $transparentNew);
                }
            }
        }
    }

    /**
     * Crée une ressource image à partir d'un fichier.
     */
    private function createImageFromFile(string $path, ?string $mimeType): ?\GdImage
    {
        return match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path) ?: null,
            'image/png' => imagecreatefrompng($path) ?: null,
            'image/webp' => imagecreatefromwebp($path) ?: null,
            'image/gif' => imagecreatefromgif($path) ?: null,
            default => null,
        };
    }

    /**
     * Sauvegarde une image dans le format approprié.
     */
    private function saveImage(\GdImage $image, string $path, ?string $mimeType, int $quality): void
    {
        // Créer le répertoire si nécessaire
        $directory = \dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        match ($mimeType) {
            'image/jpeg' => imagejpeg($image, $path, $quality),
            'image/png' => imagepng($image, $path, (int) round((100 - $quality) / 10)),
            'image/webp' => imagewebp($image, $path, $quality),
            'image/gif' => imagegif($image, $path),
            default => imagejpeg($image, $path, $quality),
        };
    }
}
