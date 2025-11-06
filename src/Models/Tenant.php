<?php

namespace Tocaan\Dukan\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $guarded = [];

    public function delete()
    {
        $this->deleteDomains();
        parent::delete();
    }

    public function deleteDomains()
    {
        $this->domains->each(function ($domain) {
            $domain->delete();
        });
    }

    public function status()
    {
        return $this->hasMany(TenantStatusLog::class);
    }

   public static function getCustomColumns(): array
   {
       return [
           'id',
           'name',
           "email",
           "is_up"
       ];
   }
}
