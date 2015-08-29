<?php namespace Codesleeve\Stapler\File;

interface LocalFileInterface extends FileInterface
{
    /**
     * Return the real path to the file.
     *
     * @return string
     */
    public function getRealPath();

}
