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
            // workaround solution for a value key that contains dot.
            $newValue = array_get($key, [], $this->values);
            $newValue[$valueKey] = $value;

            $modified = array_set($key, $newValue, $this->values);
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

/**
 * @param string|\Closure|null $classOrClosure
 * @param array $values
 * @param object|null $context
 * @param string|null $contextKey
 *
 * @return object
 */
function build_object_ref($classOrClosure = null, array &$values, $context = null, $contextKey = null)
{
    if (false == $classOrClosure) {
        foreach (get_registered_hooks('build_object', 'get_object_class') as $callback) {
            if ($classOrClosure = call_user_func($callback, $values, $context, $contextKey)) {
                break;
            }
        }
    }

    if (false == $classOrClosure) {
        if ($context) {
            throw new \LogicException(sprintf(
                'Cannot built object for %s::%s. Either class or closure has to be passed explicitly or there must be a hook that provide an object class. Values: %s',
                get_class($context),
                $contextKey,
                str_pad(var_export($values, true), 100)
            ));
        } else {
            throw new \LogicException(sprintf(
                'Cannot built object. Either class or closure has to be passed explicitly or there must be a hook that provide an object class. Values: %s',
                str_pad(var_export($values, true), 100)
            ));
        }
    }

    if ($classOrClosure instanceof \Closure) {
        $class = $classOrClosure($values);
    } else {
        $class = (string) $classOrClosure;
    }

    $object = new $class();

    //values set in constructor
    $defaultValues = get_values($object);
    $values = array_replace($defaultValues, $values);

    set_values($object, $values, true);

    if ($context) {
        foreach (get_registered_hooks($context, 'post_build_sub_object') as $callback) {
            call_user_func($callback, $object, $context, $contextKey);
        }
    } else {
        foreach (get_registered_hooks($object, 'post_build_object') as $callback) {
            call_user_func($callback, $object);
        }
    }

    return $object;
}

/**
 * @param string|\Closure|null $classOrClosure
 * @param array $values
 *
 * @return object
 */
function build_object($classOrClosure = null, array $values)
{
    return build_object_ref($classOrClosure, $values);
}

function clone_object($object)
{
    return build_object(get_class($object), get_values($object));
}

function register_cast_hooks($objectOrClass = null) {
    $castValueHook = function($object, $key, $value) {
        return (function($key, $value) {
            if (method_exists($this, 'castValue')) {
                return $this->castValue($value);
            }
        })->call($object, $key, $value);
    };

    $castToHook = function($object, $key, $value, $default, $castTo) {
        return (function($key, $value, $default, $castTo) use ($value) {
            if (method_exists($this, 'cast')) {
                return $castTo ? $this->cast($value, $castTo) : $value;
            }
        })->call($object, $key, $value, $default, $castTo);
    };

    if ($objectOrClass) {
        register_hook($objectOrClass, 'pre_set_value', $castValueHook);
        register_hook($objectOrClass, 'pre_add_value', $castValueHook);
        register_hook($objectOrClass, 'post_get_value', $castToHook);
    } else {
        register_global_hook('pre_set_value', $castValueHook);
        register_global_hook('pre_add_value', $castValueHook);
        register_global_hook('post_get_value', $castToHook);
    }
}

function call()
{
    $args = func_get_args();

    /** @var object $object */
    $object = array_shift($args);

    /** @var \Closure $closure */
    $closure = array_pop($args);

    return $closure->call($object, ...$args);
}
