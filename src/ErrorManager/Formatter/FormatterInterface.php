<?php
/**
 * @date        21/07/13
 * @author      rte
 * @file        FormatterInterface.php
 * @copyright   Copyright (c) Foyer - All rights reserved
 * @license     Unauthorized copying of this source code, via any medium is strictly
 *              prohibited, proprietary and confidential.
 */

namespace ErrorManager\Formatter;

/**
 * Class FormatterInterface
 * @package ErrorManager\Formatter
 */
interface FormatterInterface
{
    public function format(\Exception $exception, array $options);
}