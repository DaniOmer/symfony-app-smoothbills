<?php

namespace App\Form\DataTransformer;

use App\Service\FileUploaderService;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class FileToUrlTransformer implements DataTransformerInterface
{
    private $fileUploader;

    public function __construct(FileUploaderService $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function transform($value)
    {
        if (is_string($value)) {
            try {
                $tempFile = tempnam(sys_get_temp_dir(), 'uploaded');
                if ($tempFile === false) {
                    throw new \Exception('Unable to create a temporary file.');
                }
                
                $fileContent = file_get_contents($value);
                if ($fileContent === false) {
                    throw new \Exception('Unable to download the file content.');
                }

                file_put_contents($tempFile, $fileContent);
                return new File($tempFile);

            } catch (\Exception $e) {
                return null;
            }
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if ($value instanceof File) {
            return $this->fileUploader->upload($value);
        }

        return $value;
    }
}