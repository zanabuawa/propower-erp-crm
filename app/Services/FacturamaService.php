<?php

namespace App\Services;

use Facturama\Client as FacturamaClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;

class FacturamaService
{
    const SANDBOX_URL    = 'https://apisandbox.facturama.mx';
    const PRODUCTION_URL = 'https://api.facturama.mx';

    protected FacturamaClient $client;

    public function __construct()
    {
        $mode        = config('facturama.mode', 'sandbox');
        $credentials = config("facturama.{$mode}");
        $baseUri     = $mode === 'production' ? self::PRODUCTION_URL : self::SANDBOX_URL;

        $guzzle = new GuzzleClient([
            RequestOptions::HEADERS         => ['User-Agent' => FacturamaClient::USER_AGENT],
            RequestOptions::AUTH            => [$credentials['username'], $credentials['password']],
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::TIMEOUT         => 60,
            RequestOptions::VERIFY          => \GuzzleHttp\Utils::defaultCaBundle(),
        ]);

        $this->client = new FacturamaClient(null, null, [], $guzzle);
        $this->client->setApiUrl($baseUri);
    }

    public function client(): FacturamaClient
    {
        return $this->client;
    }
}
