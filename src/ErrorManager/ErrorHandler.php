<?php
/**
 * @date        21/07/13
 * @author      rte
 * @file        ErrorHandler.php
 * @copyright   Copyright (c) Foyer - All rights reserved
 * @license     Unauthorized copying of this source code, via any medium is strictly
 *              prohibited, proprietary and confidential.
 */

namespace ErrorManager;

use Zend\EventManager\EventManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

/**
 * Class ErrorHandler
 * @package ErrorManager
 */
class ErrorHandler extends \Zend\Stdlib\ErrorHandler
{
    /**
     * @var MvcEvent
     */
    protected static $event;

    /**
     * @var array
     */
    protected static $types = [
        E_ERROR             => 'Fatal Error',
        E_WARNING           => 'Warning',
        E_PARSE             => 'Parsing Error',
        E_NOTICE            => 'Notice',
        E_CORE_ERROR        => 'Core Error',
        E_CORE_WARNING      => 'Core Warning',
        E_COMPILE_ERROR     => 'Compile Error',
        E_COMPILE_WARNING   => 'Compile Warning',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error'
    ];

    /**
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @throws ErrorException
     */
    public static function addError($errno, $errstr = '', $errfile = '', $errline = 0)
    {
        $errstr = '{ PHP ' . static::$types[$errno] . ' } ' . $errstr;
        $stack  = & static::$stack[count(static::$stack) - 1];
        $stack  = new Exception\ErrorException($errstr, 0, $errno, $errfile, $errline, $stack);
    }
}