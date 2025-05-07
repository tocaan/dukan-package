<?php

namespace Tocaan\Dukan\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;


class TenantStatusChanged implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    //tes package

    /**
     * @var
     */
    public $tenant;
    /**
     * @var
     */
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct($tenant, $newStatus)
    {
        if (is_null($tenant) || is_null($newStatus)) {
            throw new \InvalidArgumentException('Tenant and status must be provided.');
        }

        $this->tenant = $tenant;
        $this->newStatus = $newStatus;
    }
}
