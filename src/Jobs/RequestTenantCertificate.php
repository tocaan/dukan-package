<?php

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Tocaan\Dukan\Services\PloiService;

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
    }
}
