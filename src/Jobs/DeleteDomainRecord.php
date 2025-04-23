<?php

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Domain;
use Tocaan\Dukan\Services\CloudflareService;
use Tocaan\Dukan\Services\PloiService;

class DeleteDomainRecord implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Domain $domain;

    /**
     * Create a new job instance.
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Execute the job.
     */
    public function handle(CloudflareService $cloudflare, PloiService $ploiService): void
    {
        logger("delete domain record", ["domain" => $this->domain->domain]);
        $this->removeDomainFromCloudflare($cloudflare);
        $this->deleteDomainFromPloi($ploiService);
    }


    protected function deleteDomainFromPloi(PloiService $ploiService): void
    {
        $ploiService->deleteTenant(config('services.ploi.site_id'), $this->domain->domain);
    }

    

    protected function removeDomainFromCloudflare(CloudflareService $cloudflare): void
    {
        try {
            // Assuming you store the Cloudflare record ID in the domain model
            // You might need to add this column to your domains table
            if ($this->domain->dns_id) {
                $cloudflare->deleteDnsRecord($this->domain->dns_id);
                Log::info('Successfully deleted DNS record for domain', [
                    'domain' => $this->domain->domain
                ]);
            } else {
                Log::warning('No Cloudflare record ID found for domain', [
                    'domain' => $this->domain->domain
                ]);
            }


        } catch (\Exception $e) {
            Log::error('Failed to delete DNS record for domain', [
                'domain' => $this->domain->domain,
                'error' => $e->getMessage()
            ]);
        }
    }   
}
