<?php

namespace Fram\Upload;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Class used to upload files.
 */
abstract class Uploader
{
    /**
     * @var string
     */
    protected $uploadDir;

    /**
     * @var array
     */
    protected $allowedTypes = [];

    /**
     * Uploads the file in the upload directory.
     *
     * @param UploadedFileInterface $file The file to upload.
     * @return string The name of the file uploaded.
     */
    public function upload(UploadedFileInterface $file): string
    {
        $this->checkType($file);

        $path = $this->uploadDir . DIRECTORY_SEPARATOR . $file->getClientFilename();
        $dirname = pathinfo($path, PATHINFO_DIRNAME);
        if (!file_exists($dirname)) {
            mkdir($dirname, 777, true);
        }

        $file->moveTo($path);
        return pathinfo($path)['dirname'] . DIRECTORY_SEPARATOR . pathinfo($path)['basename'];
    }

    /**
     * Checks whether the file type is allowed.
     *
     * @param UploadedFileInterface $file
     * @throws InvalidTypeException
     */
    private function checkType(UploadedFileInterface $file)
    {
        $extension = mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new InvalidTypeException();
        }
    }
}
