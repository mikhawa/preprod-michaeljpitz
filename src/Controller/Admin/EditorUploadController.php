<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\ImageResizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class EditorUploadController extends AbstractController
{
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private const ALLOWED_DOCUMENT_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'application/zip',
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 Mo
    private const MAX_IMAGE_WIDTH = 1200; // Largeur maximale en pixels

    public function __construct(
        private readonly ImageResizer $imageResizer,
    ) {
    }

    #[Route('/admin/editor/upload', name: 'admin_editor_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        if ('XMLHttpRequest' !== $request->headers->get('X-Requested-With')) {
            return new JsonResponse(['error' => 'Requête non autorisée.'], Response::HTTP_FORBIDDEN);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (null === $file) {
            return new JsonResponse(['error' => 'Aucun fichier envoyé.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$file->isValid()) {
            return new JsonResponse(['error' => 'Fichier invalide.'], Response::HTTP_BAD_REQUEST);
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return new JsonResponse(['error' => 'Le fichier dépasse la taille maximale de 5 Mo.'], Response::HTTP_BAD_REQUEST);
        }

        $mimeType = $file->getMimeType();
        $isImage = \in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true);
        $isDocument = \in_array($mimeType, self::ALLOWED_DOCUMENT_TYPES, true);

        if (!$isImage && !$isDocument) {
            return new JsonResponse(
                ['error' => 'Type de fichier non autorisé. Types acceptés : images (JPEG, PNG, WebP, GIF) et documents (PDF, DOC, DOCX, ODT, ZIP).'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        /** @var string $projectDir */
        $projectDir = $this->getParameter('kernel.project_dir');
        $uploadDir = $projectDir.'/public/uploads/articles/content';
        $filename = bin2hex(random_bytes(16)).'.'.($file->guessExtension() ?? 'bin');
        $destinationPath = $uploadDir.'/'.$filename;

        if ($isImage) {
            $this->imageResizer->resize($file, $destinationPath, self::MAX_IMAGE_WIDTH);
        } else {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $file->move($uploadDir, $filename);
        }

        $url = '/uploads/articles/content/'.$filename;

        return new JsonResponse([
            'url' => $url,
            'filename' => $file->getClientOriginalName(),
            'contentType' => $mimeType,
        ]);
    }
}
