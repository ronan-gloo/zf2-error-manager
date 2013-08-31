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
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class ErrorManager
 * @package ErrorManager
 */
class ErrorListener implements ListenerAggregateInterface, EventManagerAwareInterface
{
    use ListenerAggregateTrait;
    use EventManagerAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * @var Configuration
     */
    protected $config;


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
        if ($this->getConfig()->getConvertError() !== false)
        {
            $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, [$this, 'registerErrorHandler'], -100);
        }

        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onException'], -1);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onNotFound'],  -1);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR,   [$this, 'onException'], -1);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH,       [$this, 'onEvent'],     -1);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER,         [$this, 'onEvent'],     -1);
    }

    /**
     * @param Configuration $config
     */
    public function setConfig(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *
     */
    public function registerErrorHandler(MvcEvent $e)
    {
        register_shutdown_function([$this, 'onEvent'], $e);

        return $this;
    }

    /**
     * Capture Error converted to exception, then trigger a dispatch error
     * @param MvcEvent $e
     */
    public function onEvent(MvcEvent $event)
    {
        $exception = ErrorHandler::stop();

        if ($exception instanceof \Exception)
        {
            $event->setError(Application::ERROR_EXCEPTION);
            $event->setParam('exception', $exception);

            $this->getEventManager()->trigger($event::EVENT_DISPATCH_ERROR, $event);
        }
        elseif (! $event->getError() && $event->getResponse()->getStatusCode() == 404)
        {
            $event->setError(Application::ERROR_CONTROLLER_CANNOT_DISPATCH);
            $this->getEventManager()->trigger($event::EVENT_DISPATCH_ERROR, $event);
        }
    }

    /**
     * Catch exceptions here
     *
     * @param MvcEvent $e
     */
    public function onException(MvcEvent $e)
    {
        switch ($e->getError())
        {
            case Application::ERROR_EXCEPTION:

                $formatter = $this->getServiceLocator()->get('errormanager.formatter.exception');
                $exception = $e->getParam('exception');

                $content['exceptions'] = $formatter->format($exception, $this->getConfig()->toArray());
                $content['options']    = $this->getConfig()->toArray();

                // The View exception strategy inject the 'exception' event parameter to the view
                $e->getResult()->setVariable('data', $content)->setVariable('options', $content['options']);
                break;
        }
    }

    /**
     * Capture the router not match event
     *
     * @param MvcEvent $e
     */
    public function onNotFound(MvcEvent $e)
    {
        if ($error = $e->getError())
        {
            $options = $this->getConfig()->toArray();

            switch ($error)
            {
                case Application::ERROR_CONTROLLER_NOT_FOUND:
                case Application::ERROR_CONTROLLER_INVALID:
                case Application::ERROR_CONTROLLER_CANNOT_DISPATCH:
                    $formatter = $this->getServiceLocator()->get('errormanager.formatter.controller');
                    $variables = $formatter->format($e, $options);
                    $variables['options'] = $options;
                    $e->getResult()->setTemplate('error/controller')->setVariables($variables);
                    break;

                case Application::ERROR_ROUTER_NO_MATCH:
                    $formatter = $this->getServiceLocator()->get('errormanager.formatter.route');
                    $variables = $formatter->format($e, $options);
                    $variables['options'] = $options;
                    $e->getResult()->setTemplate('error/route')->setVariables($variables);
                    break;
            }
        }
    }
}