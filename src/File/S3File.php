<?php namespace Codesleeve\Stapler\File;

use Aws\S3\S3Client;
use CodeSleeve\Stapler\Stapler;
use CodeSleeve\Stapler\Factories\File as FileFactory;
use Codesleeve\Stapler\Config\ConfigurableInterface as ConfigurableInstance;

class S3File implements S3FileInterface
{
    /**
     * Standard approach to checking for image type.
     */
    use MimeCheckingTrait;

    /**
     * Provide a caching key.
     */
    use CachingKeyTrait;

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
     * S3 client configuration.
     * @var array
     */
    public $s3_client_config;

    /**
     * S3 default object configuration.
     * @var array
     */
    public $s3_object_config;

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
     * @param Codesleeve\Stapler\Config\ConfigurableInterface $config
     */
    public function __construct($filePath, ConfigurableInstance $config)
    {
        $config = $config->get('s3');
        $this->s3_client_config = $config['s3_client_config'];
        $this->s3_object_config = $config['s3_object_config'];

        $this->s3Client = Stapler::getS3ClientInstance($this);

        $this->filePath = $filePath;

        $bucket = $this->s3_object_config['Bucket'];

        /* This object makes no sense without these properties... */
        $this->metadata = $this->s3Client->headObject([
            'Bucket' => $bucket,
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
        return $this->metadata['ContentLength'];
    }

    /**
     * Return the mime type of the file.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->metadata['ContentType'];
    }

    /**
     * Return the S3 key for this file.
     *
     * @return string
     */
    public function getS3Key()
    {
        return $this->filePath;
    }

    /**
     * Return the S3 bucket for this file.
     *
     * @return string
     */
    public function getS3Bucket()
    {
        return $this->s3_object_config['Bucket'];
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
            'Bucket' => $this->getS3Bucket(),
            'Key' => $this->getS3Key(),
            'SaveAs' => $localFilePath
        ]);

        return FileFactory::create($localFilePath);
    }
}
