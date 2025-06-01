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
            $result = $this->s3->createBucket([
                'Bucket' => $bucketName,
            ]);
            $this->waitForBucketToExist($bucketName);
            $this->setBucketPolicy($bucketName);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create database in AWS S3: {$e->getMessage()}");
        }
    }


    public function waitForBucketToExist(string $bucket, int $timeoutSeconds = 10)
    {
        $start = time();
        do {
            try {
                $result = $this->s3->headBucket(['Bucket' => $bucket]);
                return true; // Bucket exists
            } catch (AwsException $e) {
                if ($e->getStatusCode() === 404) {
                    sleep(1); // wait 1 second
                } else {
                    throw $e; // throw any other unexpected error
                }
            }
        } while ((time() - $start) < $timeoutSeconds);

        throw new \Exception("Bucket {$bucket} did not become available in time.");
    }


    public function setBucketPolicy(string $bucketName): \Aws\Result
    {
        try {

            $this->s3->putPublicAccessBlock([
                'Bucket' => $bucketName,
                'PublicAccessBlockConfiguration' => [
                    'BlockPublicAcls'       => false,
                    'IgnorePublicAcls'      => false,
                    'BlockPublicPolicy'     => false,
                    'RestrictPublicBuckets' => false,
                ],
            ]);

            $policy = json_encode([
                'Version' => '2012-10-17',
                'Statement' => [[
                    'Sid' => 'PublicReadGetObject',
                    'Effect' => 'Allow',
                    'Principal' => '*',
                    'Action' => 's3:GetObject',
                    'Resource' => "arn:aws:s3:::{$bucketName}/*"
                ]]
            ]);
            return $this->s3->putBucketPolicy([
                'Bucket' => $bucketName,
                'Policy' => $policy
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to set database in AWS S3: {$e->getMessage()}");
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
