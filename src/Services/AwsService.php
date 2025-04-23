<?php

namespace Tocaan\Dukan\Services;

use Aws\S3\S3Client;

class AwsService
{

    protected S3Client $s3;

    public function __construct()
    {
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => config('dukan.s3.region'),
            'credentials' => [
                'key' => config('dukan.s3.key'),
                'secret' => config('dukan.s3.secret'),
            ],
        ]);
    }

    /**
     * get List of buckets in AWS S3
     *
     * @throws \Exception
     */
    function listBuckets(): array
    {
        try {
            return $this->s3->listBuckets()['Buckets'] ?? [];
        } catch (\Exception $e) {
            throw new \Exception("Failed to fitch database in AWS S3: {$e->getMessage()}");
        }
    }

    /**
     * Create a new bucket in AWS S3
     *
     * @param string $bucketName bucket name
     * @throws \Exception
     */
    public function createBucket(string $bucketName): \Aws\Result
    {
        try {
            return $this->s3->createBucket([
                'Bucket' => $bucketName,
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to create database in AWS S3: {$e->getMessage()}");
        }
    }

    /**
     * Delete a existing bucket in AWS S3
     *
     * @param string $bucketName bucket name
     * @throws \Exception
     */
    public function deleteBucket(string $bucketName): \Aws\Result
    {
        try {
            $objects = $this->s3->listObjects(['Bucket' => $bucketName]);
            if (!empty($objects['Contents'])) {
                foreach ($objects['Contents'] as $object) {
                    $this->s3->deleteObject([
                        'Bucket' => $bucketName,
                        'Key' => $object['Key'],
                    ]);
                }
            }
            return $this->s3->deleteBucket(['Bucket' => $bucketName]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to delete database in AWS S3: {$e->getMessage()}");
        }
    }

    /**
     * get All files in a bucket
     *
     * @param string $bucketName bucket name
     * @throws \Exception
     */

    function filesBucket(string $bucketName): array
    {
        try {
            $objects = $this->s3->listObjects(['Bucket' => $bucketName]);
            return $objects['Contents'] ?? [];
        } catch (\Exception $e) {
            throw new \Exception("Failed to fitch database in AWS S3: {$e->getMessage()}");
        }
    }

}
