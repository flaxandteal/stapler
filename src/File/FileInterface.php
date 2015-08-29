<?php namespace Codesleeve\Stapler\File;

use Codesleeve\Stapler\StoredInterface;

interface FileInterface extends StoredInterface
{
    /**
     * Return the name of the file.
     *
     * @return string
     */
    public function getFilename();

    /**
     * Return the size of the file.
     *
     * @return string
     */
    public function getSize();

    /**
     * Return the mime type of the file.
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Method for determining whether the uploaded file is
     * an image type.
     *
     * @return boolean
     */
    public function isImage();

    /**
     * Method for retrieving a (possibly temporary) local
     * version of this file.
     *
     * @return Codesleeve\Stapler\File\FilesystemFile
     */
    public function localize();
}
