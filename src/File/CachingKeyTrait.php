<?php namespace CodeSleeve\Stapler\File;

trait CachingKeyTrait
{
    /**
     * Retrieve a key that is unique to stored items during this request.
     *
     * @returns string 
     */
    function getCachingKey()
    {
        $className = get_class($this);
        $filename = $this->getFilename();

        return "file.$className.$filename";
    }
}
