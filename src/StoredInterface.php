<?php namespace Codesleeve\Stapler;

/* Not to be confused with StorageableInterface, this is something that
 * can be stored (not used as storage) */
interface StoredInterface
{
    /**
     * Retrieve a key that is unique to stored items during this request.
     *
     * @returns string 
     */
    function getCachingKey();
}
