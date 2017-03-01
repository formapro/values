<?php
namespace Makasim\Values;

/**
 * @param object $object
 * @param array $values
 * @param bool $byReference
 *
 * @return object
 */
function set_values($object, array &$values, $byReference = false)
{
    $func = (function (array &$values, $byReference) {
        if ($byReference) {
            $this->values = &$values;
        } else {
            $this->values = $values;
        }

        foreach (get_registered_hooks($this, 'post_set_values') as $callback) {
            call_user_func($callback, $this, $values, $byReference);
        }

        return $this;
    })->bindTo($object, $object);

    return $func($values, $byReference);
}

function get_values($object)
{
    return (function () { return $this->values; })->call($object);
}

function add_value($object, $key, $value, $valueKey = null)
{
    return (function($key, $value, $valueKey) {
        foreach (get_registered_hooks($this, 'pre_add_value') as $callback) {
            if (null !== $changedValue = call_user_func($callback, $this, $key, $value)) {
                $value = $changedValue;
            }
        }

        $newValue = array_get($key, [], $this->values);
        if (false == is_array($newValue)) {
            throw new \LogicException(sprintf('Cannot set value to %s it is already set and not array', $key));
        }

        if (null === $valueKey) {
            $newValue[] = $value;

            end($newValue);
            $valueKey = key($newValue);
            reset($newValue);

            $modified = array_set($key, $newValue, $this->values);
        } else {
            $modified = array_set($key.'.'.$valueKey, $value, $this->values);
        }

        foreach (get_registered_hooks($this, 'post_add_value') as $callback) {
            call_user_func($callback, $this, $key.'.'.$valueKey, $value, $modified);
        }

        return $valueKey;
    })->call($object, $key, $value, $valueKey);
}

function set_value($object, $key, $value)
{
    return (function($key, $value) {
        foreach (get_registered_hooks($this, 'pre_set_value') as $callback) {
            if (null !== $newValue = call_user_func($callback, $this, $key, $value)) {
                $value = $newValue;
            }
        }

        if (null !== $value) {
            $modified = array_set($key, $value, $this->values);
        } else {
            $modified = array_unset($key, $this->values);
        }

        foreach (get_registered_hooks($this, 'post_set_value') as $callback) {
            call_user_func($callback, $this, $key, $value, $modified);
        }
    })->call($object, $key, $value);
}

function get_value($object, $key, $default = null, $castTo = null)
{
    return (function($key, $default, $castTo) {
        $value = array_get($key, $default , $this->values);

        foreach (get_registered_hooks($this, 'post_get_value') as $callback) {
            if (null !== $newValue = call_user_func($callback, $this, $key, $value, $default, $castTo)) {
                $value = $newValue;
            }
        }

        return $value;
    })->call($object, $key, $default, $castTo);
}


// TODO tobe reviewed

function get_object_changed_values($object)
{
    return (function () {
        $changedValues = $this->changedValues;

        // hack I know
        if (property_exists($this, 'objects')) {
            foreach ($this->objects as $namespace => $namespaceValues) {
                foreach ($namespaceValues as $name => $values) {
                    if (is_array($values)) {
                        foreach ($values as $valueKey => $value) {
                            if ($changed = get_object_changed_values($value)) {
                                $changedValues[$namespace][$name][$valueKey] = $changed;
                            }
                        }
                    } elseif (is_object($values)) {
                        if ($changed = get_object_changed_values($values)) {
                            $changedValues[$namespace][$name] = $changed;
                        }
                    }
                }
            }
        }

        return $changedValues;
    })->call($object);
}