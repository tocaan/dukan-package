<?php

declare(strict_types=1);

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Tocaan\Dukan\Events\TenantStatusChanged;
use Tocaan\Dukan\Services\CloudflareService;
use Tocaan\Dukan\Services\PloiService;

class CreateTenantWithPloi implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected TenantWithDatabase $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): void
    {
        $ploiService = app(PloiService::class);
        $cloudService = app(CloudflareService::class);
        $domains = $this->tenant->domains;
        Log::info("Creating tenant with ploi", ["domains" => $domains]);
        event(new TenantStatusChanged($this->tenant, 'tenant_created_in_ploi'));
        foreach ($domains as $domain) {
            $cloudResponse = $cloudService->addDnsRecord('A', $domain->domain, config("services.cloudflare.ip"));
            $dnsId = Arr::get($cloudResponse, 'result.id');
            if ($dnsId) {
                $domain->dns_id = $dnsId;
                $domain->saveQuietly();
            }
            Log::info("Adding dns record", ["domain" => $domain->domain, "response" => $cloudResponse]);
            event(new TenantStatusChanged($this->tenant, 'dns_record_added'));
        }
        $pluckDomains = $domains->pluck('domain')->toArray();
        $ploiService->createTenant(config('services.ploi.site_id'), $pluckDomains);
        Log::info("Requesting certificate", ["domains" => $pluckDomains]);
        event(new TenantStatusChanged($this->tenant, 'certificate_requested'));
        dispatch(new RequestTenantCertificate($this->tenant))->delay(60);
    }
}
