<?php

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Tocaan\Dukan\Services\PloiService;

class DeleteTenantDatabase implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }
    
    public function handle(PloiService $ploiService)
    {
        $database = $this->tenant->getInternal('db_id');
        logger("delete database", ["database" => $database]);
        if (!$database) {
            return;
        }

        // Delete database using Ploi
        $result = $ploiService->deleteDatabase(
            id: $database
        );
        logger("delete database result", ["result" => $result]);
    }
} 