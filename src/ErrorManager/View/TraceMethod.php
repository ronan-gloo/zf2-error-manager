<?php

namespace ErrorManager\View;

use Zend\View\Helper\AbstractHelper;

/**
 * Class TraceMethod
 * @package ErrorManager\View
 */
class TraceMethod extends AbstractHelper
{
    public function __invoke($trace)
    {
        if ($trace['class'] == 'ErrorManager\ErrorHandler')
        {
            return '<strong class="muted">' . $trace['args'][1] . '</strong>';
        }

        $pattern =
            '<strong class="text-info">%s</strong>'
            .'<var class="muted">%s</var>'
            .'<span class="text-success">%s(<samp class="text-error">%s</samp>)</span>'
        ;

        return sprintf($pattern,
            $trace['class'],
            $trace['type'],
            $trace['function'],
            $this->mapArgs($trace['args'])
        );
    }

    protected function mapArgs($args)
    {
        $mapped = array_map(function($arg)
        {
            switch ($type = gettype($arg))
            {
                case 'array':
                case 'resource':
                    return '$'.$type;

                case 'object':
                    return get_class($arg);

                default:
                    return var_export($arg, true);
            }
        }, $args);

        return implode('<span class="muted">,</span> ', $mapped);
    }
}