<?php

namespace ErrorManager\Formatter;

/**
 * Class RouteFormatter
 * @package ErrorManager\Formatter
 */
class RouteFormatter implements FormatterInterface
{
    /**
     * @param       \Zend\Mvc\MvcEvent  $event
     * @param array                     $options
     *
     * @return array
     */
    public function format($event, array $options)
    {
        /** @var \Zend\Http\Request $request */
        $request = $event->getRequest();
        $httpUri = $request->getUri();
        $router  = $event->getRouter();
        $locator = $event->getApplication()->getServiceManager();

        $data['request']['method'] = $request->getMethod();

        $data['uri']['port']       = $httpUri->getPort();
        $data['uri']['scheme']     = $httpUri->getScheme();
        $data['uri']['host']       = $httpUri->getHost();
        $data['uri']['path']       = $httpUri->getPath();

        $data['params']['get']     = $httpUri->getQueryAsArray();
        $data['params']['post']    = $request->getPost()->toArray();

        $translator = $locator->get('translator');
        $data['locale'] = $translator->getLocale();

        // Router information
        $config = $locator->get('config');
        $data['router']['default_parameters'] = isset($config['router']['default_params'])
            ? $config['router']['default_params']
            : null;

        $data['router']['class_name'] = get_class($router);
        $data['router']['count']      = $router->getRoutes()->count();

        return $data;
    }
}