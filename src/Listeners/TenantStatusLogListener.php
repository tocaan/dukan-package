<?php

namespace Tocaan\Dukan\Listeners;
//use App\Models\TenantStatusLog;
use Tocaan\Dukan\Events\TenantStatusChanged;
use Tocaan\Dukan\Models\TenantStatusLog;

class TenantStatusLogListener
{
    /**
     * The event listener's priority.
     *
     * @var int
     */
    public static int $priority = 0;

    /**
     * The event listener's events.
     *
     * @var array
     */
    public static array $events = [
        TenantStatusChanged::class,
    ];

    /**
     * The event listener's subscribers.
     *
     * @var array
     */
    public static array $subscribers = [];

    /**
     * The event listener's name.
     *
     * @var string
     */
    public static string $name = 'TenantStatusLogListener';

    /**
     * The event listener's description.
     *
     * @var string
     */
    public static string $description = 'Log tenant status changes';

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TenantStatusChanged $event): void
    {
        TenantStatusLog::updateOrCreate(
            ['tenant_id' => $event->tenant->id, 'status' => $event->newStatus],
            ['tenant_id' => $event->tenant->id, 'status' => $event->newStatus]
        );

        $statuses = [
            's3_bucket_created',
            'database_created',
            'tenant_created_in_ploi',
            'dns_record_added',
            'certificate_requested',
        ];
        $logs = TenantStatusLog::where('tenant_id', $event->tenant->id)->whereIn('status', $statuses)->count();
        if ($logs >= 5) {
            $event->tenant->update(['is_up' => true]);
        }
    }
}
