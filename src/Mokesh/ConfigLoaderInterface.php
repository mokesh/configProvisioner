<?php

namespace Mokesh;

/**
 * ConfigLoaderInterface
 */
interface ConfigLoaderInterface
{
    /**
     * Load the Configuration to the provided ArrayAccess implementor 
     *
     * @param ArrayAccess $array
     * @return void
     */
    public function load(\ArrayAccess $array);
}
