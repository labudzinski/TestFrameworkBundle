<?php

namespace Labudzinski\TestFrameworkBundle\Component\Layout\Loader\Driver;

use Labudzinski\TestFrameworkBundle\Component\Layout\LayoutUpdateInterface;

interface DriverInterface
{
    /**
     * Load/generate layout update instance based on given file resource.
     *
     * @param string $file
     *
     * @return LayoutUpdateInterface
     */
    public function load($file);

    /**
     * Return pattern of layout update filename
     *
     * @param string $fileExtension
     *
     * @return string
     */
    public function getUpdateFilenamePattern($fileExtension);
}
