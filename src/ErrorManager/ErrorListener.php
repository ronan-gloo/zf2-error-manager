<?php

namespace ErrorManager;

use ErrorManager\ErrorHandler;
use ErrorManager\Formatter\FormatterInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

/**
 * Class ErrorManager
 * @package ErrorManager
 */
class ErrorListener implements ListenerAggregateInterface, EventManagerAwareInterface
{
    use ListenerAggregateTrait;
    use EventManagerAwareTrait;

    /**
     * Error level to none
     */
    const E_NONE = 0;

    /**
     * @var array
     */
    protected $options = [
        'precision'         => 10,
        'displayTrace'      => true,
        'displayPrevious'   => true,
        'displayDocBlock'   => true,
        'assets'            => null,
    ];

    /**
     * @var int
     */
    protected $convertError = self::E_NONE;

    /**
     * @var Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * Setup options if any
     *
     * @param array $options
     *
     * @throws Exception\BadMethodCallException
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = [])
    {
        $options and $this->fromArray($options);
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onEventError'], 100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'onEventError'], 100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onEvent'], -1);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, [$this, 'onEvent'], -1);
    }

    /**
     * Set options by converting array keys 'my_key' to 'setMyKey' and call the setter
     *
     * @param $options
     *
     * @return $this
     * @throws Exception\BadMethodCallException
     * @throws Exception\InvalidArgumentException
     */
    public function fromArray($options)
    {
        if (! is_array($options) && ! $options instanceof \Traversable)
        {
            throw new Exception\InvalidArgumentException(sprintf(
                'Precision must be a valid numeric value, "%s" given',
                is_object($options) ? get_class($options) : gettype($options)

            ));
        }

        foreach ($options as $key => $option)
        {
            $method = 'set' . ucwords(str_replace(['_', ' '], [' ', ''], $key));

            if (! method_exists($this, $method))
            {
                throw new Exception\BadMethodCallException(sprintf(
                    '"%s": The configuration key is not supported', $key
                ));
            }
            $this->$method($option);
        }

        return $this;
    }

    /**
     * @param int $precision
     *
     * @throws Exception\InvalidArgumentException
     * @return $this
     */
    public function setPrecision($precision)
    {
        $this->options['precision'] = $this->toInt($precision);

        return $this;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->options['precision'];
    }

    /**
     * @param bool|int $value
     *
     * @return $this
     */
    public function setDisplayTrace($value)
    {
        $this->options['displayTrace'] = is_bool($value) ? $value : $this->toInt($value);

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getDisplayTrace()
    {
        return $this->options['displayTrace'];
    }

    /**
     * @param bool|int $value
     *
     * @return $this
     */
    public function setDisplayPrevious($value)
    {
        $this->options['displayPrevious'] = is_bool($value) ? $value : $this->toInt($value);

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisplayDocBlock()
    {
        return $this->options['displayDocBlock'];
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setDisplayDocBlock($value)
    {
        $this->options['displayDocBlock'] = (bool) $value;

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getDisplayPrevious()
    {
        return $this->options['displayPrevious'];
    }


    /**
     * @param array $assets
     *
     * @return $this
     */
    public function setAssets(array $assets)
    {
        $this->options['assets'] = $assets;

        return $this;
    }

    public function getAssets()
    {
        return $this->options['assets'];
    }

    /**
     * @param int $convertError
     *
     * @return $this
     */
    public function setConvertError($convertError)
    {
        $value = is_bool($convertError)
               ? $convertError ? E_ALL : static::E_NONE
               : $this->toInt($convertError);

        $this->convertError = $value;

        // Refresh the handler if necessary
        ErrorHandler::started() and ErrorHandler::clean();

        if ($this->convertError !== static::E_NONE)
        {
            ErrorHandler::start($value);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getConvertError()
    {
        return $this->convertError;
    }

    /**
     * @param $value
     *
     * @return int
     * @throws Exception\InvalidArgumentException
     */
    protected function toInt($value)
    {
        if (! is_numeric($value))
        {
            throw new Exception\InvalidArgumentException(sprintf(
                'Argument must be a valid numeric value, "%s" given',
                is_object($value) ? get_class($value) : (is_scalar($value) ? $value : gettype($value))

            ));
        }
        return intval($value);
    }

    /**
     * @param \ErrorManager\Formatter\FormatterInterface $formatter
     *
     * @return $this
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @return \ErrorManager\Formatter\FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Capture Error converted to exception, then trigger a dispatch error
     * @param MvcEvent $e
     */
    public function onEvent(MvcEvent $e)
    {
        $exception = ErrorHandler::stop();

        if ($exception instanceof \Exception)
        {
            $e->setError(Application::ERROR_EXCEPTION);
            $e->setParam('exception', $exception);

            $this->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $e);
        }
    }

    /**
     * Catch exceptions here
     *
     * @param MvcEvent $e
     */
    public function onEventError(MvcEvent $e)
    {
        switch ($e->getError())
        {
            case Application::ERROR_EXCEPTION:
                $exception = $e->getParam('exception');
                $content['exceptions'] = $this->getFormatter()->format($exception, $this->options);
                $content['options']    = $this->options;
                // The View exception strategy inject the 'exception' event parameter to the view
                $e->setParam('exception', $content);
                break;
        }
    }
}