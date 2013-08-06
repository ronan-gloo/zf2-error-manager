<?php

namespace ErrorManager\View;

use Zend\View\Helper\AbstractHelper;

/**
 * Class fileName
 * @package ErrorManager\View
 */
class FileName extends AbstractHelper
{
    /**
     * @param string $filename
     *
     * @return string
     */
    public function __invoke($filename)
    {
        return $this->format($filename);
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function format($filename)
    {
        $baseName = basename($filename);
        $filePath = substr($filename, strlen(getcwd()));
        $filePath = ltrim(str_replace($baseName, '', $filePath), '/');

        return sprintf('<code class="text-info">%s</code> <code>%s</code>', $filePath, $baseName);
    }
}