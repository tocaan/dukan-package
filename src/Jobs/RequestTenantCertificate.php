<?php

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Tocaan\Dukan\Services\PloiService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Tocaan\Dukan\Events\TenantStatusChanged;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class RequestTenantCertificate implements ShouldQueue
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

    public function handle()
    {
        $ploiService = app(PloiService::class);
        $this->tenant->domains->each(function ($domainModel) use ($ploiService) {
            $ploiService->requestCertificate(config('dukan.ploi.site_id'), $domainModel->domain, [$domainModel->domain]);
        });
        event(new TenantStatusChanged($this->tenant, 'certificate_requested'));

    }
}
