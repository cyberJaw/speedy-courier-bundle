<?php
namespace Cyberjaw\SpeedyCourierBundle\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

final class SpeedyClient
{
    private ?string $token = null;

    public function __construct(
        private readonly ClientInterface $http,
        private readonly string $baseUri,
        private readonly string $username,
        private readonly string $password,
        private readonly float  $timeout = 20.0,
        private readonly bool   $sandbox = true,
    ) {}

    /** Логин – вземи token/sessionId според Speedy API ти */
    public function authenticate(): void
    {
        $resp = $this->http->request('POST', $this->path('/user/login'), [
            'timeout' => $this->timeout,
            'json'    => ['username' => $this->username, 'password' => $this->password],
        ]);
        $data = $this->json($resp);
        $this->token = $data['token'] ?? $data['sessionId'] ?? null;
        if (!$this->token) {
            throw new \RuntimeException('Speedy auth failed: missing token/sessionId');
        }
    }

    public function getOffices(array $filters = []): array
    {
        return $this->authed('GET', '/offices', ['query' => $filters]);
    }

    public function validateAddress(array $address): array
    {
        return $this->authed('POST', '/address/validate', ['json' => ['address' => $address]]);
    }

    public function calculate(array $payload): array
    {
        return $this->authed('POST', '/shipments/calculate', ['json' => $payload]);
    }

    public function createShipment(array $payload): array
    {
        return $this->authed('POST', '/shipments', ['json' => $payload]);
    }

    public function getLabel(string $shipmentId, string $format = 'pdf'): string
    {
        $resp = $this->request('GET', "/shipments/{$shipmentId}/label", [
            'query' => ['format' => $format],
        ]);
        return (string) $resp->getBody(); // binary PDF/ZPL
    }

    public function cancelShipment(string $shipmentId): array
    {
        return $this->authed('DELETE', "/shipments/{$shipmentId}");
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    private function authed(string $method, string $path, array $opts = []): array
    {
        if (!$this->token) $this->authenticate();
        $resp = $this->request($method, $path, $opts);
        return $this->json($resp);
    }

    private function request(string $method, string $path, array $opts = []): ResponseInterface
    {
        $headers = $opts['headers'] ?? [];
        if ($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        $opts['headers'] ??= [];
        $opts['headers'] += ['Accept' => 'application/json'];
        $opts['timeout'] ??= $this->timeout;

        try {
            return $this->http->request($method, $this->path($path), $opts + ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Speedy request error: '.$e->getMessage(), previous: $e);
        }
    }

    private function json(ResponseInterface $resp): array
    {
        $status = $resp->getStatusCode();
        $body   = (string) $resp->getBody();
        $data   = json_decode($body, true);

        if ($status >= 400) {
            throw new \RuntimeException("Speedy API HTTP {$status}: ".$body);
        }
        if ($data === null && $body !== '' /* allow empty body */) {
            throw new \RuntimeException("Speedy API invalid JSON response: ".$body);
        }
        return $data ?? [];
    }

    private function path(string $path): string
    {
        return rtrim($this->baseUri, '/') . $path;
    }
}
