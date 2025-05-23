<?php

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Tocaan\Dukan\Events\TenantStatusChanged;
use Tocaan\Dukan\Services\PloiService;

class CreateTenantDatabase extends CreateDatabase
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

    public function handle(DatabaseManager $databaseManager)
    {
        event(new CreatingDatabase($this->tenant));

        // Terminate execution of this job & other jobs in the pipeline
        if ($this->tenant->getInternal('create_database') === false) {
            return false;
        }

        $key = $this->tenant->{config('dukan.tenancy.identifier')};
        $key = str_replace(["-"," "], "_", $key);
        $database = 'tenancy_db_' . $key;
        $username = 'user_' . Str::random(8);
        $password = Str::random(16);

        $ploiService = app(PloiService::class);

       

        // $this->tenant->setInternal('db_name', $database);
        // $this->tenant->setInternal('db_username', $username);
        // $this->tenant->setInternal('db_password', $password);
        // Create database using Ploi
        $ploiDatabase = $ploiService->createDatabase(
            name: $database,
            user: $username,
            password: $password
        );
        logger("create database",$ploiDatabase);
        // $this->tenant->setInternal('db_id', Arr::get($ploiDatabase, 'data.id'));
        // $this->tenant->save()
        
        $this->tenant->update([
            'tenancy_db_name' => $database,
            'tenancy_db_username' => $username,
            'tenancy_db_password' => $password,
            'tenancy_db_id' => Arr::get($ploiDatabase, 'data.id'),
        ]);
       
        event(new TenantStatusChanged($this->tenant, 'database_created'));
        sleep(4);
        event(new DatabaseCreated($this->tenant));
    }

}
