<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use RuntimeException;

class FacturapiService
{
    protected GuzzleClient $http;

    public function __construct()
    {
        $mode   = config('facturapi.mode', 'test');
        $apiKey = $mode === 'production'
            ? config('facturapi.live_key')
            : config('facturapi.test_key');

        $this->http = new GuzzleClient([
            'base_uri'                    => rtrim(config('facturapi.base_url', 'https://www.facturapi.io/v2'), '/') . '/',
            RequestOptions::HEADERS       => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::TIMEOUT         => 60,
        ]);
    }

    /**
     * Timbrar una factura CFDI 4.0.
     *
     * @param  array $payload  Estructura FacturAPI v2 (customer, items, etc.)
     * @return object           Respuesta decodificada con ->id y ->uuid
     */
    public function createInvoice(array $payload): object
    {
        return $this->post('invoices', $payload);
    }

    /**
     * Cancelar una factura ante el SAT.
     *
     * @param  string      $facturApiId      ID interno de FacturAPI (no el UUID SAT)
     * @param  string      $motive           Clave SAT: '01', '02', '03', '04'
     * @param  string|null $substitution     UUID de sustitución (solo motive '01')
     * @return object
     */
    public function cancelInvoice(string $facturApiId, string $motive, ?string $substitution = null): object
    {
        $body = ['motive' => $motive];
        if ($motive === '01' && $substitution) {
            $body['substitution'] = $substitution;
        }

        return $this->post("invoices/{$facturApiId}/cancel", $body);
    }

    /**
     * Descargar el PDF de una factura como contenido binario.
     */
    public function downloadPdf(string $facturApiId): string
    {
        return $this->downloadFile("invoices/{$facturApiId}/pdf");
    }

    /**
     * Descargar el XML de una factura como contenido binario.
     */
    public function downloadXml(string $facturApiId): string
    {
        return $this->downloadFile("invoices/{$facturApiId}/xml");
    }

    // ── Helpers privados ───────────────────────────────────────────────────

    private function post(string $endpoint, array $body): object
    {
        try {
            $response = $this->http->post($endpoint, [
                RequestOptions::JSON => $body,
            ]);

            return json_decode($response->getBody()->getContents());

        } catch (ClientException $e) {
            $body    = $e->getResponse()->getBody()->getContents();
            $decoded = json_decode($body);
            $message = $decoded->message ?? $body;

            throw new RuntimeException($message, $e->getCode(), $e);
        }
    }

    private function downloadFile(string $endpoint): string
    {
        try {
            $response = $this->http->get($endpoint, [
                RequestOptions::HEADERS => ['Accept' => '*/*'],
            ]);

            return $response->getBody()->getContents();

        } catch (ClientException $e) {
            $body    = $e->getResponse()->getBody()->getContents();
            $decoded = json_decode($body);
            $message = $decoded->message ?? $body;

            throw new RuntimeException($message, $e->getCode(), $e);
        }
    }
}
