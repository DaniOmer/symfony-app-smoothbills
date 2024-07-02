<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploaderService
{
    private $s3Client;
    private $bucketName;

    public function __construct(S3Client $s3Client, ParameterBagInterface $params)
    {
        $this->s3Client = $s3Client;
        $this->bucketName = $params->get('aws_s3_bucket_name');
    }

    public function upload(File $file): string
    {
        $filePath = $file->getRealPath();
        $fileName = uniqid() . '.' . $file->guessExtension();

        $result = $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key'    => $fileName,
            'SourceFile' => $filePath,
            'ACL'    => 'public-read',
        ]);

        return $result->get('ObjectURL');
    }
}
