<?php namespace Codesleeve\Stapler\File;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class File extends SymfonyFile implements LocalFileInterface
{
    /**
     * Standard approach to checking for image type.
     */
    use MimeCheckingTrait;

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
