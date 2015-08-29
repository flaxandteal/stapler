<?php namespace Codesleeve\Stapler\Storage;

use Aws\S3\S3Client;
use Codesleeve\Stapler\Attachment;

class S3 implements StorageableInterface
{
    /**
     * The current attachedFile object being processed.
     *
     * @var \Codesleeve\Stapler\Attachment
     */
    public $attachedFile;

    /**
     * The AWS S3Client instance.
     *
     * @var S3Client
     */
    protected $s3Client;

    /**
     * Boolean flag indicating if this attachment's bucket currently exists.
     *
     * @var array
     */
    protected $bucketExists = false;

    /**
     * Constructor method
     *
     * @param Attachment $attachedFile
     * @param S3Client $s3Client
     */
    function __construct(Attachment $attachedFile, S3Client $s3Client)
    {
        $this->attachedFile = $attachedFile;
        $this->s3Client = $s3Client;
    }

    /**
     * Return the url for a file upload.
     *
     * @param  string $styleName
     * @return string
     */
    public function url($styleName)
    {
        return $this->s3Client->getObjectUrl($this->attachedFile->s3_object_config['Bucket'], $this->path($styleName), null, ['PathStyle' => true]);
    }

    /**
     * Return the key the uploaded file object is stored under within a bucket.
     *
     * @param  string $styleName
     * @return string
     */
    public function path($styleName)
    {
        return $this->attachedFile->getInterpolator()->interpolate($this->attachedFile->path, $this->attachedFile, $styleName);
    }

    /**
     * Remove an attached file.
     *
     * @param  array $filePaths
     */
    public function remove(array $filePaths)
    {
        if ($filePaths) {
            $this->s3Client->deleteObjects(['Bucket' => $this->attachedFile->s3_object_config['Bucket'], 'Objects' => $this->getKeys($filePaths)]);
        }
    }

    /**
     * Move an uploaded file to its intended destination.
     *
     * @param  CodeSleeve\Stapler\File\FileInterface $file
     * @param  string $filePath
     */
    public function move($file, $filePath)
    {
        if ($file instanceof CodeSleeve\Stapler\File\S3FileInterface)
        {
            $this->moveOnS3($file, $filePath);
        }
        else
        {
            $this->moveToS3($file, $filePath);
        }
    }

    /**
     * Move a file on S3 to another location in the bucket.
     *
     * @param  CodeSleeve\Stapler\File\S3FileInterface $file
     * @param  string $filePath
     */
    public function moveOnS3($file, $filePath)
    {
        $objectConfig = $this->attachedFile->s3_object_config;

        $fileSpecificConfig = [
            'Key' => $filePath,
            'CopySource' => $file->getRealPath(),
        ];
        $mergedConfig = array_merge($objectConfig, $fileSpecificConfig);

        $this->ensureBucketExists($mergedConfig['Bucket']);
        $this->s3Client->copyObject($mergedConfig);

        $fileSpecificConfig = [
            'Key' => $file->getRealPath(),
        ];
        $this->s3Client->deleteObject($mergedConfig);
    }

    /**
     * Move an uploaded file to its intended destination via
     * the filesystem.
     *
     * @param  CodeSleeve\Stapler\File\FileInterface $file
     * @param  string $filePath
     */
    public function moveToS3($file, $filePath)
    {
        $localFile = $file->localize();
        $objectConfig = $this->attachedFile->s3_object_config;
        $fileSpecificConfig = ['Key' => $filePath, 'SourceFile' => $localFile->getRealPath(), 'ContentType' => $this->attachedFile->contentType()];
        $mergedConfig = array_merge($objectConfig, $fileSpecificConfig);

        $this->ensureBucketExists($mergedConfig['Bucket']);
        $this->s3Client->putObject($mergedConfig);
    }

    /**
     * Return an array of paths (bucket keys) for an attachment.
     * There will be one path for each of the attachmetn's styles.
     *
     * @param  $filePaths
     * @return array
     */
    protected function getKeys($filePaths)
    {
        $keys = [];

        foreach ($filePaths as $filePath) {
            $keys[] = ['Key' => $filePath];
        }

        return $keys;
    }

    /**
     * Ensure that a given S3 bucket exists.
     *
     * @param  string $bucketName
     */
    protected function ensureBucketExists($bucketName)
    {
        if (!$this->bucketExists) {
            $this->buildBucket($bucketName);
        }
    }

    /**
     * Attempt to build a bucket (if it doesn't already exist).
     *
     * @param  string $bucketName
     */
    protected function buildBucket($bucketName)
    {
        if (!$this->s3Client->doesBucketExist($bucketName, true)) {
            $this->s3Client->createBucket(['ACL' => $this->attachedFile->ACL, 'Bucket' => $bucketName, 'LocationConstraint' => $this->attachedFile->region]);
        }

        $this->bucketExists = true;
    }
}
