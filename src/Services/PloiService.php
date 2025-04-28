<?php

namespace Tocaan\Dukan\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PloiService
{
    private const API_BASE_URL = 'https://ploi.io/api';
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 30; // seconds

    private string $apiToken;
    private int $serverId;

    public function __construct()
    {
        $this->apiToken = config('dukan.ploi.api_token');
        $this->serverId = config('dukan.ploi.server_id');
    }

    /**
     * Create a new database in Ploi
     *
     * @param string $name Database name
     * @param string $user Database user
     * @param string $password Database password
     * @return array Database details
     * @throws \Exception
     */
    public function createDatabase(string $name, string $user, string $password): array
    {
        try {
            $response = $this->makeRequest('post', "servers/{$this->serverId}/databases", [
                'name' => $name,
                'user' => $user,
                'password' => $password,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Ploi database creation failed', [
                'error' => $e->getMessage(),
                'database' => $name,
            ]);
            
            throw new \Exception("Failed to create database in Ploi: {$e->getMessage()}");
        }
    }

    /**
     * Create a new tenant for a site in Ploi
     *
     * @param int $siteId The ID of the site
     * @param string|array $tenants Single domain or array of domains
     * @return array Tenant details
     * @throws \Exception
     */
    public function createTenant(int|string $siteId, string|array $tenants): array
    {
        try {
            // Convert single string to array if necessary
            $tenantDomains = is_string($tenants) ? [$tenants] : $tenants;
            
            $response = $this->makeRequest('post', "servers/{$this->serverId}/sites/{$siteId}/tenants", [
                'tenants' => $tenantDomains,
            ]);

            Log::info('Ploi tenant created successfully', [
                'site_id' => $siteId,
                'tenants' => $tenants,
                'response' => $response->json(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Ploi tenant creation failed', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
                'tenants' => $tenants,
            ]);
            
            throw new \Exception("Failed to create tenant in Ploi: {$e->getMessage()}");
        }
    }

    /**
     * Request SSL certificate for a tenant
     *
     * @param int $siteId The ID of the site
     * @param string $tenant The tenant domain
     * @param string|array $domains Additional domains for the certificate
     * @return array Certificate request details
     * @throws \Exception
     */
    public function requestCertificate(int|string $siteId, string $tenant, string|array $domains): array
    {
        $attempts = 0;
        
        while ($attempts < self::MAX_RETRIES) {
            try {
                // Check if DNS has propagated first
                if ($this->isDnsPropagated($domains)) {
                    $response = $this->makeRequest('post', "servers/{$this->serverId}/sites/{$siteId}/tenants/{$tenant}/request-certificate", [
                        'domains' => is_string($domains) ? $domains : implode(',', $domains),
                    ]);
                    
                    if ($response->successful()) {
                        Log::info('SSL certificate requested successfully', [
                            'site_id' => $siteId,
                            'tenant' => $tenant,
                            'domains' => $domains
                        ]);
                        return $response->json();
                    }
                }
                
                $attempts++;
                if ($attempts < self::MAX_RETRIES) {
                    Log::info('Waiting for DNS propagation before retry', [
                        'attempt' => $attempts,
                        'site_id' => $siteId,
                        'tenant' => $tenant,
                        'domains' => $domains
                    ]);
                    sleep(self::RETRY_DELAY);
                }
            } catch (\Exception $e) {
                $attempts++;
                Log::error('Error requesting SSL certificate', [
                    'attempt' => $attempts,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempts >= self::MAX_RETRIES) {
                    throw $e;
                }
                sleep(self::RETRY_DELAY);
            }
        }
        
        throw new \Exception('Failed to request SSL certificate after ' . self::MAX_RETRIES . ' attempts');
    }

    /**
     * Delete a tenant from a site in Ploi
     *
     * @param int $siteId The ID of the site
     * @param string $tenant The tenant domain to delete
     * @return bool
     * @throws \Exception
     */
    public function deleteTenant(int|string $siteId, string $tenant): bool
    {
        try {
            $endpoint = "servers/{$this->serverId}/sites/{$siteId}/tenants/" . urlencode(trim($tenant));
            
            $response = Http::withToken($this->apiToken)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(30) // Add timeout
                ->delete($this->getFullUrl($endpoint));
            if ($response->successful()) {
                Log::info('Ploi tenant deleted successfully', [
                    'site_id' => $siteId,
                    'tenant' => $tenant,
                ]);
                return true;
            }

            throw new \Exception($response->body() ?: 'No response from Ploi API');
        } catch (\Exception $e) {
            Log::error('Ploi tenant deletion failed', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
                'tenant' => $tenant,
                'url' => $this->getFullUrl("servers/{$this->serverId}/sites/{$siteId}/tenants/" . urlencode($tenant)),
                'token_exists' => !empty($this->apiToken),
            ]);
            
            throw new \Exception("Failed to delete tenant in Ploi: {$e->getMessage()}");
        }
    }

    /**
     * Delete a database in Ploi
     *
     * @param string $id Database id
     * @return array Response details
     * @throws \Exception
     */
    public function deleteDatabase(string $id): mixed
    {
        try {
            $response = $this->makeRequest('delete', "servers/{$this->serverId}/databases/{$id}");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Ploi database deletion failed', [
                'error' => $e->getMessage(),
                'database' => $id,
            ]);
            
            throw new \Exception("Failed to delete database in Ploi: {$e->getMessage()}");
        }
    }

    /**
     * Make an HTTP request to Ploi API
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return Response
     * @throws \Exception
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->$method($this->getFullUrl($endpoint), $data);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response;
    }

    /**
     * Get the full API URL
     */
    private function getFullUrl(string $endpoint): string
    {
        return self::API_BASE_URL . '/' . trim($endpoint, '/');
    }

    private function isDnsPropagated(array $domains): bool
    {
        foreach ($domains as $domain) {
            // Get the expected IP from your configuration
            $expectedIp = config('dukan.cloudflare.ip');
            
            // Perform DNS lookup
            $ips = gethostbynamel($domain);
            
            if (!$ips || !in_array($expectedIp, $ips)) {
                Log::info('DNS not yet propagated', [
                    'domain' => $domain,
                    'expected_ip' => $expectedIp,
                    'current_ips' => $ips ?? 'none'
                ]);
                return false;
            }
        }
        
        return true;
    }
} 