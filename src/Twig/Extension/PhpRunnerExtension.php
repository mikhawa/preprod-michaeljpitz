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
     *
     * Suneditor stocke le texte multiligne dans des balises <p>...</p> séparées
     * par des \n. Sans traitement, </p>\n<p> génère des sauts doubles parasites.
     * Le bouton PHP du toolbar insère les lignes avec <br> dans un seul <p>.
     */
    private function buildWidget(string $rawCode): string
    {
        // Décoder les entités HTML introduites par Suneditor (&amp; &lt; &quot; etc.)
        $code = html_entity_decode($rawCode, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Fusionner les paires </p><p> (multi-paragraphes Suneditor) en un seul saut de ligne
        $code = (string) preg_replace('#</p>\s*<p[^>]*>#i', "\n", $code);
        // Supprimer les balises <p> et </p> résiduelles (début/fin de blocs)
        $code = (string) preg_replace('#<p[^>]*>#i', '', $code);
        $code = (string) preg_replace('#</p>#i', '', $code);

        // Convertir les <br> en sauts de ligne (insertion via le bouton PHP ou WYSIWYG)
        $code = (string) preg_replace('#<br\s*/?>(\n)?#i', "\n", $code);

        // Supprimer toute balise HTML résiduelle (spans d'indentation Suneditor, etc.)
        $code = strip_tags($code);

        // Convertir les espaces insécables (NBSP produits par Suneditor) en espaces normaux
        $code = str_replace("\u{00A0}", ' ', $code);

        // Limiter à une ligne vide maximum entre les blocs de code (évite les blancs excessifs)
        $code = (string) preg_replace('/\n{3,}/', "\n\n", $code);

        $code = trim($code);

        return sprintf(
            '<div data-controller="php-runner" data-php-runner-code-value="%s"></div>',
            htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }
}
