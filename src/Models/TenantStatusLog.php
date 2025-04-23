<?php

namespace Tocaan\Dukan\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant;

class TenantStatusLog extends Model
{
    protected $fillable = ['tenant_id', 'status'];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
