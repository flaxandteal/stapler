<?php namespace Codesleeve\Stapler\File;

use Aws\S3\S3Client;
use CodeSleeve\Stapler\Factories\File as FileFactory;

class S3File implements FileInterface
{
    /**
     * Standard approach to checking for image type.
     */
    use MimeCheckingTrait;

    /**
     * The AWS S3Client instance.
     *
     * @var S3Client
     */
    protected $s3Client;

    /**
     * Location in S3 bucket.
     *
     * @var string
     */
    protected $filePath;

    /**
     * S3 configuration.
     * @var array
     */
    protected $config;

    /**
     * File factory for producing local files
     * @var Codesleeve\Stapler\Factories\File
     */
    protected $fileFactory;


    /**
     * Initialize this class with an available S3Client to allow
     * localization when required (this should be a singleton, so
     * cheap to re-use)
     *
     * @param string $filePath
     * @param Aws\S3\S3Client $s3Client
     */
    public function __construct($filePath, $config, S3Client $s3Client, FileFactory $fileFactory)
    {
        $this->s3Client = $s3Client;
        $this->fileFactory = $fileFactory;

        $this->filePath = $filePath;

        /* This object makes no sense without these properties... */
        $this->metadata = $this->s3Client->headObject([
            'Bucket' => $this->config['s3_object_config']['Bucket'],
            'Key' => $this->filePath
        ]);
    }

    /**
     * Return the name of the file.
     *
     * @return string
     */
    public function getFilename()
    {
        return basename($this->filePath);
    }

    /**
     * Return the size of the file.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->metadata['Content-Length'];
    }

    /**
     * Return the mime type of the file.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->metadata['Content-Type'];
    }

    /**
     * Method for retrieving a (possibly temporary) local
     * version of this file.
     *
     * @return Codesleeve\Stapler\File\LocalFileInterface
     */
    public function localize()
    {
        $name = $this->getFilename();

        $localFilePath = sys_get_temp_dir() . "/$name";

        $file = $this->s3Client->getObject([
            'Bucket' => $this->config['s3_object_config']['Bucket'],
            'Key' => $this->filePath,
            'SaveAs' => $localFilePath
        ]);

        return $this->fileFactory->create($localFilePath);
    }
}
