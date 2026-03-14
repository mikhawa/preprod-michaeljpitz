<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Injecte le code de suivi Matomo dans toutes les réponses HTML.
 * Actif uniquement en production pour ne pas fausser les statistiques.
 */
class TrackingCodeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        #[Autowire('%env(MATOMO_URL)%')]
        private readonly string $matomoUrl,
        #[Autowire('%env(MATOMO_SITE_ID)%')]
        private readonly string $matomoSiteId,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // Actif uniquement en production
        if ('prod' !== $this->environment) {
            return;
        }

        // Ne pas injecter si l'URL Matomo n'est pas configurée
        if ('' === $this->matomoUrl || '' === $this->matomoSiteId) {
            return;
        }

        $response = $event->getResponse();
        $contentType = $response->headers->get('Content-Type') ?? '';

        // Injecter uniquement dans les réponses HTML
        if (!str_contains($contentType, 'text/html')) {
            return;
        }

        $content = $response->getContent();
        if (false === $content) {
            return;
        }

        // Injecter le script Matomo juste avant </head>
        $matomoUrl = htmlspecialchars($this->matomoUrl, ENT_QUOTES, 'UTF-8');
        $matomoSiteId = htmlspecialchars($this->matomoSiteId, ENT_QUOTES, 'UTF-8');

        $trackingScript = <<<MATOMO
<!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="{$matomoUrl}";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '{$matomoSiteId}']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->
MATOMO;

        $content = str_replace('</head>', $trackingScript."\n</head>", $content);
        $response->setContent($content);
    }
}
