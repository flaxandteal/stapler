<?php namespace Codesleeve\Stapler\File;

/**
 * This interface is simply to indicate a file may be
 * manipulated directly on the S3 instance.
 */
interface S3FileInterface extends FileInterface
{

    /**
     * Return the S3 key for this file.
     *
     * @return string
     */
    public function getS3Key();

    /**
     * Return the S3 bucket for this file.
     *
     * @return string
     */
    public function getS3Bucket();
}
