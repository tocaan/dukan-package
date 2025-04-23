<?php

namespace Tocaan\Dukan\Models;

use Illuminate\Database\Eloquent\Model;

class TenantStatusLog extends Model
{
    protected $fillable = ['tenant_id', 'status'];
}
