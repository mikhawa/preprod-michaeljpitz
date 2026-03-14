<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TurnstileValidator
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $secretKey,
    ) {
    }

    public function validate(string $token, ?string $remoteIp = null): bool
    {
        // Bypass en mode développement avec clés de test Cloudflare
        if (empty($this->secretKey)
            || 'YOUR_TURNSTILE_SECRET_KEY' === $this->secretKey
            || str_starts_with($this->secretKey, '1x00000000000000000000')
        ) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $data = [
            'secret' => $this->secretKey,
            'response' => $token,
        ];

        if (null !== $remoteIp) {
            $data['remoteip'] = $remoteIp;
        }

        try {
            $response = $this->httpClient->request('POST', self::VERIFY_URL, [
                'body' => $data,
            ]);

            $result = $response->toArray();

            return $result['success'] ?? false;
        } catch (\Exception) {
            return false;
        }
    }
}
