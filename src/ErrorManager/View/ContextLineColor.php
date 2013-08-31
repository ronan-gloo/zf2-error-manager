<?php

namespace ErrorManager\View;

use Zend\View\Helper\AbstractHelper;

/**
 * Display colors according the Namespace of the file / exception
 *
 * Class ContextLineColor
 * @package ErrorManager\View
 */
class ContextLineColor extends AbstractHelper
{
    /**
     * @param array $exception
     * @param array $trace
     *
     * @return string
     */
    public function __invoke(array $trace)
    {
        //$namespace = substr($trace['class'], 0, strpos($trace['class'], '\\'));

        if (strpos($trace['file'], 'zendframework') !== false)
        {
            $status = 'default';
        }
        elseif (strpos($trace['file'], 'vendor') !== false)
        {
            $status = 'warning';
        }
        else
        {
            $status = 'info';
        }

        return sprintf('<small class="label label-%s">@%s</small>', $status, $trace['line']);
    }
}