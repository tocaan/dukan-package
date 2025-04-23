<?php

namespace Tocaan\Dukan\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PloiService
{
    private const API_BASE_URL = 'https://ploi.io/api';

    private string $apiToken;
    private int $serverId;

    public function __construct()
    {
        $this->apiToken = config('services.ploi.api_token');
        $this->serverId = config('services.ploi.server_id');
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
    public function createTenant(int $siteId, string|array $tenants): array
    {
        try {
            // Convert single string to array if necessary
            $tenantDomains = is_string($tenants) ? [$tenants] : $tenants;
            
            $response = $this->makeRequest('post', "servers/{$this->serverId}/sites/{$siteId}/tenants", [
                'tenants' => $tenantDomains,
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
    public function requestCertificate(int $siteId, string $tenant, string|array $domains): array
    {
        try {
            // Convert domains to array if string and join with commas
            $domainList = is_string($domains) ? $domains : implode(',', $domains);
            $response = $this->makeRequest(
                'post',
                "servers/{$this->serverId}/sites/{$siteId}/tenants/{$tenant}/request-certificate",
                // ['domains' => $domainList]
            );
        

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Ploi SSL certificate request failed', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
                'tenant' => $tenant,
                'domains' => $domains,
            ]);
            
            throw new \Exception("Failed to request SSL certificate in Ploi: {$e->getMessage()}");
        }
    }

    /**
     * Delete a tenant from a site in Ploi
     *
     * @param int $siteId The ID of the site
     * @param string $tenant The tenant domain to delete
     * @return bool
     * @throws \Exception
     */
    public function deleteTenant(int $siteId, string $tenant): bool
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
} 