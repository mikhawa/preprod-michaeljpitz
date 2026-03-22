<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HtmlSanitizerExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire(service: 'html_sanitizer.sanitizer.comment_sanitizer')]
        private readonly HtmlSanitizerInterface $commentSanitizer,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sanitize_comment', [$this, 'sanitizeComment'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Supprime tout le HTML d'un contenu de commentaire (texte pur uniquement).
     */
    public function sanitizeComment(string $content): string
    {
        return $this->commentSanitizer->sanitize($content);
    }
}
