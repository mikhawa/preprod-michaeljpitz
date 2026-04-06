<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Filtre Twig `php_runner` : convertit les marqueurs [php]...[/php]
 * présents dans le contenu d'un article en widgets PHP interactifs.
 *
 * L'auteur écrit dans Suneditor (mode WYSIWYG ou code source) :
 *   [php]echo PHP_VERSION;[/php]
 *
 * Le filtre remplace ce marqueur par un <div data-controller="php-runner">
 * contenant le code encodé. Suneditor ne modifie jamais ce texte brut.
 */
class PhpRunnerExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('php_runner', [$this, 'processPhpRunners'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Remplace chaque bloc [php]...[/php] par un widget Stimulus php-runner.
     * Gère les cas où Suneditor a enveloppé le marqueur dans une balise <p>.
     */
    public function processPhpRunners(string $content): string
    {
        // Cas 1 : <p>[php]...[/php]</p>  (mode WYSIWYG, Suneditor ajoute <p>)
        $content = (string) preg_replace_callback(
            '#<p[^>]*>\s*\[php\](.*?)\[/php\]\s*</p>#si',
            fn (array $m): string => $this->buildWidget($m[1]),
            $content
        );

        // Cas 2 : [php]...[/php] sans <p> (mode code source)
        $content = (string) preg_replace_callback(
            '#\[php\](.*?)\[/php\]#si',
            fn (array $m): string => $this->buildWidget($m[1]),
            $content
        );

        return $content;
    }

    /**
     * Construit le HTML du widget à partir du code brut extrait du contenu.
     * Décode les entités HTML et normalise les sauts de ligne.
     */
    private function buildWidget(string $rawCode): string
    {
        // Décoder les entités HTML introduites par Suneditor (&amp; &lt; &quot; etc.)
        $code = html_entity_decode($rawCode, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Convertir les <br> en sauts de ligne (Suneditor convertit \n en <br>)
        $code = (string) preg_replace('#<br\s*/?>\\n?#i', "\n", $code);

        // Supprimer toute balise HTML résiduelle
        $code = strip_tags($code);

        $code = trim($code);

        return sprintf(
            '<div data-controller="php-runner" data-php-runner-code-value="%s"></div>',
            htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }
}
