<?php

namespace Tocaan\Dukan\Models;

use Illuminate\Database\Eloquent\Model;

class TenantStatusLog extends Model
{
    protected $table = 'tenant_status_logs';

    protected $fillable = ['tenant_id', 'status'];

    /**
     * Get the connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        // Always use the central connection
        return config('tenancy.database.central_connection') ?? config('database.default');
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $tenant = $model->tenant;
            $countForEvent = $tenant->TenantStatusLog()->whereIn('status', ['database_created', 'database_migrated', 'database_seeded'])->count();
            if($countForEvent == 3) {
                $tenant->status = 1;
                $tenant->save();
            }
        });
    }
}
