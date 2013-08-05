<?php

namespace ErrorManager\Formatter;

/**
 * Class ExceptionFormatter
 * @package ErrorManager\Formatter
 */
class ExceptionFormatter implements FormatterInterface
{

    /**
     * @var array
     */
    protected $fileCache = [];

    /**
     * @var array
     */
    protected $docCache  = [];

    /**
     * @param \Exception $exception
     * @param array      $options
     *
     * @return array
     */
    public function format($exception, array $options)
    {
        $prevCount  = 0;
        $prevLength = $options['displayPrevious'];
        $displayDoc = $options['displayDocBlock'];

        while($exception)
        {
            $errors[] =
            [
                'name'          => get_class($exception),
                'message'       => $exception->getMessage(),
                'file'          => $exception->getFile(),
                'line'          => $exception->getLine(),
                'trace'         => $this->formatTrace($exception, $options),
                'documentation' => $displayDoc ? $this->getClassDocBlock($exception) : ''
            ];

            $exception = ($prevLength === true || ++$prevCount <= $prevLength)
                ? $exception->getPrevious()
                : null;
        }

        return $errors;
    }

    /**
     * @param \Exception $exception
     * @param array      $options
     *
     * @return array
     */
    public function formatTrace(\Exception $exception, array $options)
    {
        $data = $traces = [];

        if (! $options['displayTrace'])
            return $data;

        // Filter trace nby count, and skip closures
        $count = 0;
        foreach ($exception->getTrace() as $trace)
        {
            if (++$count > $options['displayTrace'])
                break;

            if (isset($trace['class']) and class_exists($trace['class']) and isset($trace['file']))
            {
                $traces[] = $trace;
            }
        }

        foreach ($traces as $key => $trace)
        {
            $add['context']       = $this->getContext($trace, $options['precision']);
            $add['documentation'] = $options['displayDocBlock']
                ? $this->getMethodDocBlock($trace['class'], $trace['function'])
                : '';

            $data[$key] = $trace + $add;
        }

        return $data;
    }

    /**
     * Get and parse the exception class documentation.
     * We try to not display the class information
     * or comment annotations @.
     *
     * @param  $class
     * @return string
     */
    public function getClassDocBlock($class)
    {
        $class = new \ReflectionClass($class);

        if ($str = $class->getDocComment())
        {
            return $this->readDocBlock($str);
        }

        return $str;
    }

    /**
     * @param $className
     * @param $methodName
     *
     * @return string
     */
    public function getMethodDocBlock($className, $methodName)
    {
        $cacheKey = $className.$methodName;

        if (isset($this->docCache[$cacheKey]))
        {
            return $this->docCache[$cacheKey];
        }

        $class  = new \ReflectionClass($className);
        $output = '';

        // Possible magic method...
        try {
            $str = $class->getMethod($methodName)->getDocComment()
            and $output = $this->readDocBlock($str);
        }
        catch (\Exception $e){}

        $this->docCache[$cacheKey] = $output;

        return $output;
    }

    /**
     * @param $str
     * @return string
     */
    protected function readDocBlock($str)
    {
        $result = '';

        foreach (explode("\n", $str) as $line)
        {
            if ($l = str_replace(['/**', '*/'], '', $line))
            {
                $result .= ltrim(trim($l), '*')."\n";
            }
        }
        return trim($result);
    }


    /**
     * @param $trace
     * @param $precision
     *
     * @return array
     */
    public function getContext($trace, $precision)
    {
        $filename = $trace['file'];

        if (! isset($this->fileCache[$filename]))
        {
            $this->fileCache[$filename] = file($filename);
        }

        $line = intval($trace['line']);

        return array_intersect_key($this->fileCache[$filename],
            array_fill($line - $precision, ($precision * 2) - 1, null)
        );
    }
}