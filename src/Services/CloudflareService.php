<?php

namespace Tocaan\Dukan\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    private const API_BASE_URL = 'https://api.cloudflare.com/client/v4';

    private string $apiToken;
    private string $zoneId;

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
        $this->zoneId = config('services.cloudflare.zone_id');
    }

    /**
     * Add a DNS record to Cloudflare
     *
     * @param string $type The type of DNS record (e.g., A, CNAME)
     * @param string $name The name of the DNS record (e.g., subdomain.example.com)
     * @param string $content The content of the DNS record (e.g., IP address)
     * @param int $ttl Time to live for the DNS record
     * @param bool $proxied Whether the record is proxied by Cloudflare
     * @return array The response from Cloudflare
     * @throws \Exception
     */
    public function addDnsRecord(string $type, string $name, string $content, int $ttl = 3600, bool $proxied = false): array
    {
        logger("calling ");
        try {
            $response = Http::withToken($this->apiToken)
                ->acceptJson()
                ->post(self::API_BASE_URL . "/zones/{$this->zoneId}/dns_records", [
                    'type' => $type,
                    'name' => $name,
                    'content' => $content,
                    'ttl' => $ttl,
                    'proxied' => $proxied,
                ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Cloudflare DNS record creation failed', [
                'error' => $e->getMessage(),
                'type' => $type,
                'name' => $name,
                'content' => $content,
            ]);

            throw new \Exception("Failed to create DNS record in Cloudflare: {$e->getMessage()}");
        }
    }

    /**
     * Delete a DNS record from Cloudflare
     *
     * @param string $recordId The ID of the DNS record to delete
     * @return array The response from Cloudflare
     * @throws \Exception
     */
    public function deleteDnsRecord(string $recordId): array
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->acceptJson()
                ->delete(self::API_BASE_URL . "/zones/{$this->zoneId}/dns_records/{$recordId}");

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Cloudflare DNS record deletion failed', [
                'error' => $e->getMessage(),
                'record_id' => $recordId,
            ]);

            throw new \Exception("Failed to delete DNS record in Cloudflare: {$e->getMessage()}");
        }
    }
} 