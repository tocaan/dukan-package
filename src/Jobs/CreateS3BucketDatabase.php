<?php

declare(strict_types=1);

namespace Tocaan\Dukan\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Tocaan\Dukan\Events\TenantStatusChanged;
use Tocaan\Dukan\Services\AwsService;

class CreateS3BucketDatabase implements ShouldQueue
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

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $bucketName = $this->tenant->id . '-bucket-s3';
        $AwsService = app(AwsService::class);
        $s3 = $AwsService->createBucket($bucketName);
        $this->tenant->setInternal('s3_bucket', $bucketName);
        $this->tenant->setInternal('s3_url', @$s3['@metadata']['effectiveUri']);
        event(new TenantStatusChanged($this->tenant, 's3_bucket_created'));
        Log::info("Database Create S3 Bucket ", ["name" => $bucketName]);
    }
}
