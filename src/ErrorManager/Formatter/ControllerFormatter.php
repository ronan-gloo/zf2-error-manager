<?php

namespace ErrorManager\Formatter;

use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

/**
 * Class ControllerFormatter
 * @package ErrorManager\Formatter
 */
class ControllerFormatter implements FormatterInterface
{
    /**
     * @param \Zend\Mvc\MvcEvent $object
     * @param array $options
     *
     * @return array
     */
    public function format($object, array $options)
    {
        $data['route']['name']   = $object->getRouteMatch()->getMatchedRouteName();
        $data['route']['params'] = $object->getRouteMatch()->getParams();

        $parts  = explode('/', $data['route']['name']);
        $route  = $object->getRouter();
        $config = $object->getApplication()->getServiceManager()->get('config');
        $config = isset($config['router']['routes']) ? $config['router']['routes'] : [];

        while($part = array_shift($parts))
        {
            $route->hasRoute($part) and $route  = $route->getRoute($part);
            isset($config[$part])   and $config = $config[$part];
        }

        $data['route']['class']      = get_class($route);
        $data['route']['assembled']  = $route->getAssembledParams();

        $data['event']['error'] = $object->getError();
        $data['event']['name']  = $object->getName();

        $controllers = [];
        $definitions = [];
        $title       = '404 Error';
        $subtitle    = 'Unknown Error';
        $context     = null;
        $manager     = $object->getApplication()->getServiceManager()->get('ControllerLoader');

        switch ($object->getError())
        {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
                $definitions = $config;
                $title       = $object->getControllerClass();
                $subtitle    = 'The requested controller cannot be found';
                $controllers = $manager->getCanonicalNames();
                array_pop($controllers); // because the Sm add the wrong into the list
                break;

            case Application::ERROR_CONTROLLER_INVALID:
                $title    = $object->getControllerClass();
                $subtitle = $object->getParam('exception')->getMessage();
                break;

            case Application::ERROR_CONTROLLER_CANNOT_DISPATCH:

                $context  = $this->getControllerContext($manager, $data['route']['params']);
                $subtitle = 'The controller cannot dispatch the request';
                $title    = $data['route']['params']['controller'];
                break;
        }

        $data['title']                  = $title;
        $data['subtitle']              = $subtitle;
        $data['route']['definition']   = $definitions;
        $data['controller']['names']   = $controllers;
        $data['controller']['context'] = $context;

        return $data;
    }

    /**
     * Get the controller's method context
     */
    public function getControllerContext($manager, $params)
    {
        try {
            $reflection = new \ReflectionClass($manager->get($params['controller']));
            $method = $reflection->getMethod($params['action'] . 'Action');
        }
        catch (\Exception $e) {
            $array = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($array as $method)
            {
                if (strpos($method->getName(), 'Action'))
                {
                    $methods[] = $method->getName();
                }
            }
            sort($methods);
            return compact('methods');
        }

        $start  = (int) $method->getStartLine();
        $stop   = (int) $method->getEndLine();
        $file   = file($reflection->getFileName());

        return [
            'file' => array_slice($file, $start - 1, ($stop - $start + 1)),
            'doc'  => $method->getDocComment()
        ];
    }
}