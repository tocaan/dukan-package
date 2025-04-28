<?php

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Tocaan\Dukan\Services\CloudflareService;
use Tocaan\Dukan\Services\PloiService;

class DeleteTenantDomains implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tenant;
    protected $ploiService;
    protected $cloudflareService;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
        $this->ploiService = app(PloiService::class)    ;
        $this->cloudflareService = app(CloudflareService::class);
    }
    
    public function handle()
    {
        logger("delete tenant domains", ["tenant" => $this->tenant->id]);
        foreach ($this->tenant->domains as $domain) {
            $this->deleteDomainFromPloi($domain);
            $this->removeDomainFromCloudflare($domain);
        }

    
    }

    protected function deleteDomainFromPloi( $domain): void
    {
        $this->ploiService->deleteTenant(config('dukan.ploi.site_id'), $domain->domain);
    }

    

    protected function removeDomainFromCloudflare($domain): void
    {
        try {
            // Assuming you store the Cloudflare record ID in the domain model
            // You might need to add this column to your domains table
            if ($domain->dns_id) {
                $this->cloudflareService->deleteDnsRecord($domain->dns_id);
                Log::info('Successfully deleted DNS record for domain', [
                    'domain' => $domain->domain
                ]);
            } else {
                Log::warning('No Cloudflare record ID found for domain', [
                    'domain' => $domain->domain
                ]);
            }


        } catch (\Exception $e) {
            Log::error('Failed to delete DNS record for domain', [
                'domain' => $domain->domain,
                'error' => $e->getMessage()
            ]);
        }
    }   
} 