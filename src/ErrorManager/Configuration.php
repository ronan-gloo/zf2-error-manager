<?php

namespace ErrorManager;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Configuration
 * @package ErrorManager\Option
 */
class Configuration extends AbstractOptions
{
    /**
     * Error level to none
     */
    const E_NONE = 0;

    /**
     * @var int
     */
    protected $convertError = self::E_NONE;

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
        $value = is_bool($convertError) ? $convertError ? E_ALL : static::E_NONE : $this->toInt($convertError);

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

    public function toArray()
    {
        return $this->options;
    }
}