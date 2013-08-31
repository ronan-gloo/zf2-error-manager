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
        $dir      = getcwd();
        $baseName = basename($filename);
        $filePath = strpos($filename, $dir) !== false ? substr($filename, strlen($dir)) : $filename;
        $filePath = ltrim(str_replace($baseName, '', $filePath), '/');

        return sprintf('<code class="text-info">%s</code> <code>%s</code>', $filePath, $baseName);
    }
}