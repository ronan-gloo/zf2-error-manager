<?php

namespace ErrorManager\Formatter;

/**
 * Class FormatterInterface
 * @package ErrorManager\Formatter
 */
interface FormatterInterface
{
    public function format(\Exception $exception, array $options);
}