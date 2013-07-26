<?php

namespace ErrorManager;

use ErrorManager\Formatter\ExceptionFormatter;
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
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->get('config');
        $service = new ErrorListener;

        if (isset($config['error_manager']))
        {
            $service->fromArray($config['error_manager']);
        }

        $service
            ->setFormatter(new ExceptionFormatter)
            ->setEventManager($serviceLocator->get('application')->getEventManager())
        ;

        return $service;
    }
}