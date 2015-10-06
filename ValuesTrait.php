<?php
namespace Makasim\ValuesORM;

trait ValuesTrait
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $changedValues = [];

    /**
     * @param string $key
     * @param mixed  $value
     */
    protected function setSelfValue($key, $value)
    {
        $this->setValue('self', $key, $value);
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @param string $castTo
     *
     * @return mixed
     */
    protected function getSelfValue($key, $default = null, $castTo = null)
    {
        return $this->getValue('self', $key, $default, $castTo);
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
        $this->changedValues = [];

        if (property_exists($this, 'objects')) {
            $this->objects = [];
        }
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param string $value
     */
    public function addValue($namespace, $key, $value)
    {
        if ($value instanceof \DateTime) {
            $value = [
                'unix' => (int) $value->format('U'),
                'iso' => (string) $value->format(DATE_ISO8601),
            ];
        }

        $currentValue = $this->getValue($namespace, $key, []);
        if (false == is_array($currentValue)) {
            throw new \LogicException(sprintf('Cannot set value to %s.%s it is already set and not array', $namespace, $key));
        }

        $currentValue[] = $value;

        $this->setValue($namespace, $key, $currentValue);
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param string $value
     */
    public function setValue($namespace, $key, $value)
    {
        if ($value instanceof \DateTime) {
            $value = [
                'unix' => (int) $value->format('U'),
                'iso' => (string) $value->format(DATE_ISO8601),
            ];
        }

        if (null !== $value) {
            $this->values[$namespace][$key] = $value;
        } else {
            unset($this->values[$namespace][$key]);
        }

        $this->changedValues[$namespace][$key] = $value;

        if (property_exists($this, 'objects')) {
            unset($this->objects[$namespace][$key]);
        }
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param mixed  $default
     * @param string $castTo
     *
     * @return mixed
     */
    public function getValue($namespace, $key, $default = null, $castTo = null)
    {
        if (false == array_key_exists($namespace, $this->values) || false == array_key_exists($key, $this->values[$namespace])) {
            return $default;
        }

        $value = $this->values[$namespace][$key];

        if ('date' == $castTo) {
            if (is_numeric($value)) {
                $value = \DateTime::createFromFormat('U', $value);
            } elseif (is_array($value)) {
                $value = \DateTime::createFromFormat('U', $value['unix']);
            } else {
                $value = new \DateTime($value);
            }
        } elseif ($castTo) {
            settype($value, $castTo);
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getChangedValues()
    {
        $changedValues = $this->changedValues;

        // hack I know
        if (property_exists($this, 'objects')) {
            foreach ($this->objects as $namespace => $namespaceValues) {
                foreach ($namespaceValues as $name => $values) {
                    if (is_array($values)) {
                        foreach ($values as $valueKey => $value) {
                            if (false == empty($value->getChangedValues())) {
                                $changedValues[$namespace][$name][$valueKey] = $value->getChangedValues();
                            }
                        }
                    } elseif (is_object($values)) {
                        if (false == empty($values->getChangedValues())) {
                            $changedValues[$namespace][$name] = $values->getChangedValues();
                        }
                    }
                }
            }
        }

        return $changedValues;
    }
}
