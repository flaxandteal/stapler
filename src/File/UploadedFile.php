<?php namespace Codesleeve\Stapler\File;

use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Codesleeve\Stapler\Exceptions\FileException;

class UploadedFile implements LocalFileInterface
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
     * The underlying uploaded file object that acts
     * as part of this class's composition.
     *
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $uploadedFile;

    /**
     * Constructor method.
     *
     * @param SymfonyUploadedFile $uploadedFile
     */
    function __construct(SymfonyUploadedFile $uploadedFile) {
        $this->uploadedFile = $uploadedFile;
    }

    /**
     * Handle dynamic method calls on this class.
     * This method allows this class to act as a 'composite' object
     * by delegating method calls to the underlying SymfonyUploadedFile object.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->uploadedFile, $method], $parameters);
    }

    /**
     * Return the name of the file.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->uploadedFile->getClientOriginalName();
    }

    /**
     * Return the real path to the file.
     *
     * @return string
     */
    public function getRealPath()
    {
        return $this->uploadedFile->getRealPath();
    }

    /**
     * Return the size of the file.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->uploadedFile->getClientSize();
    }

    /**
     * Return the mime type of the file.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->uploadedFile->getMimeType();
    }

    /**
     * Validate the uploaded file object.
     *
     * @throws FileException
     */
    public function validate()
    {
        if (!$this->isValid()) {
            throw new FileException($this->getErrorMessage());
        }
    }

    /**
     * Returns an informative upload error message.
     *
     * @return string
     */
    protected function getErrorMessage()
    {
        $errorCode = $this->getError();

        static $errors = [
            UPLOAD_ERR_INI_SIZE   => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d kb).',
            UPLOAD_ERR_FORM_SIZE  => 'The file "%s" exceeds the upload limit defined in your form.',
            UPLOAD_ERR_PARTIAL    => 'The file "%s" was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            UPLOAD_ERR_EXTENSION  => 'File upload was stopped by a php extension.',
        ];

        $maxFilesize = $errorCode === UPLOAD_ERR_INI_SIZE ? self::getMaxFilesize() / 1024 : 0;
        $message = isset($errors[$errorCode]) ? $errors[$errorCode] : 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf($message, $this->getClientOriginalName(), $maxFilesize);
    }

    /**
     * Method for retrieving a (possibly temporary) local
     * version of this file.
     *
     * @return Codesleeve\Stapler\File\LocalFileInterface
     */
    public function localize()
    {
        /* Files represented by this class are always local (unless
         * this method is overridden in a subclass), even if the
         * attachment they will be uploaded to is not
         */
        return $this;
    }
}
