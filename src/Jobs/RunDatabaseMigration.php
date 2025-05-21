<?php

declare(strict_types=1);

namespace Stancl\Tenancy\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class RunDatabaseMigration extends MigrateDatabase
{
    

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $paths = config("tenancy.migration_parameters.--path")?? [];

        if(count(config("dukan.tenancy.modules"))){
            foreach(config("dukan.tenancy.modules") as $module){
                $paths[] = config("dukan.tenancy.modules_path")."/".$module."/Database/Migrations";
            }
        }
        
        Artisan::call('tenants:migrate', [
            '--tenants' => [$this->tenant->getTenantKey()],
            '--path' => $paths,
        ]);
    }
}
