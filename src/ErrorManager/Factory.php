<?php
/**
 * @date        21/07/13
 * @author      rte
 * @file        Factory.php
 * @copyright   Copyright (c) Foyer - All rights reserved
 * @license     Unauthorized copying of this source code, via any medium is strictly
 *              prohibited, proprietary and confidential.
 */

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