<?php

namespace ErrorManager;

use ErrorManager\Formatter\ExceptionFormatter;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Factory
 * @package ErrorManager\ExceptionHandler
 */
class Factory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $locator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $locator)
    {
        $config  = $locator->get('config');
        $service = new ErrorListener;

        if (isset($config['error_manager']))
        {
            $config = new Configuration($config['error_manager']);
            $service->setConfig($config);
        }

        $eventManager = $locator->get('application')->getEventManager();
        $service
            ->setServiceLocator($locator)
            ->setEventManager($eventManager)
        ;

        return $service;
    }
}