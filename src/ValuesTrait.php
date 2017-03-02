<?php
namespace Makasim\Values;

trait ValuesTrait
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var callable[]
     */
    protected $hookId = [];

    /**
     * @param string $key
     * @param string $value
     */
    protected function addValue($key, $value)
    {
        add_value($this, $key, $value);
    }

    /**
     * @param string $key
     * @param string $value
     */
    protected function setValue($key, $value)
    {
        set_value($this, $key, $value);
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @param string $castTo
     *
     * @return mixed
     */
    protected function getValue($key, $default = null, $castTo = null)
    {
        return get_value($this, $key, $default, $castTo);
    }
}
