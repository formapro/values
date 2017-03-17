<?php
namespace Makasim\Values;

/**
 * @param object      $context
 * @param string      $key
 * @param object|null $object
 */
function set_object($context, $key, $object)
{
    register_object_hooks($context);

    (function($key, $object) use($context) {
        if ($object) {
            register_object_hooks($object);

            set_value($this, $key, null);
            set_value($this, $key, get_values($object));

            $values =& array_get($key, [], $this->values);
            set_values($object, $values, true);

            array_set($key, $object, $this->objects);

            foreach (get_registered_hooks($context, 'post_set_object') as $callback) {
                call_user_func($callback, $object, $context, $key);
            }
        } else {
            set_value($this, $key, null);
            array_unset($key, $this->objects);
        }
    })->call($context, $key, $object);
}

/**
 * @param object $context
 * @param string $key
 * @param object[]|null $objects
 */
function set_objects($context, $key, $objects)
{
    register_object_hooks($context);

    (function($key, $objects) use ($context) {
        if (null !== $objects) {
            array_set($key, [], $this->objects);

            $objectsValues = [];
            foreach ($objects as $objectKey => $object) {
                array_set($objectKey, get_values($object), $objectsValues);
            }

            set_value($this, $key, $objectsValues);

            foreach ($objects as $objectKey => $object) {
                register_object_hooks($object);

                $values =& array_get($key.'.'.$objectKey, [], $this->values);
                set_values($object, $values, true);

                array_set($key.'.'.$objectKey, $object, $this->objects);

                foreach (get_registered_hooks($context, 'post_set_object') as $callback) {
                    call_user_func($callback, $object, $context, $key.'.'.$objectKey);
                }
            }
        } else {
            set_value($this, $key, null);
            array_unset($key, $this->objects);
        }
    })->call($context, $key, $objects);
}

/**
 * @param string $key
 * @param object $object
 * @param string|null $objectKey
 */
function add_object($context, $key, $object, $objectKey = null)
{
    register_object_hooks($context);
    register_object_hooks($object);

    (function($key, $object, $objectKey) use ($context) {
        $objectValues = get_values($object);

        $objectKey = add_value($this, $key, $objectValues, $objectKey);

        $values =& array_get($key.'.'.$objectKey, [], $this->values);
        set_values($object, $values, true);

        array_set($key.'.'.$objectKey, $object, $this->objects);

        foreach (get_registered_hooks($context, 'post_add_object') as $callback) {
            call_user_func($callback, $object, $context, $key.'.'.$objectKey);
        }

    })->call($context, $key, $object, $objectKey);
}

/**
 * @param object $object
 * @param string $key
 * @param string|\Closure|null $classOrClosure
 *
 * @return null|object
 */
function get_object($object, $key, $classOrClosure = null)
{
    return (function($key, $classOrClosure) {
        if (false == $object = array_get($key, null, $this->objects)) {
            $values =& array_get($key, null, $this->values);
            if (null === $values) {
                return;
            }

            if (false == $classOrClosure) {
                foreach (get_registered_hooks($this, 'get_object_class') as $callback) {
                    if ($classOrClosure = call_user_func($callback, $this, $key, $values)) {
                        break;
                    }
                }
            }

            if (false == $classOrClosure) {
                throw new \LogicException('Either class or closure has to be passed explicitly or there must be a hook that provide an object class.');
            }


            $object = build_object_ref($classOrClosure, $values, $this, $key);

            array_set($key, $object, $this->objects);
        }

        return $object;
    })->call($object, $key, $classOrClosure);
}

/**
 * @param string          $key
 * @param string|\Closure $classOrClosure
 *
 * @return \Traversable
 */
function get_objects($context, $key, $classOrClosure)
{
    return (function($key, $classOrClosure) {
        foreach (array_keys(array_get($key, [], $this->values)) as $valueKey) {
            if (false == $object = array_get("$key.$valueKey", null, $this->objects)) {
                if ($object = get_object($this, "$key.$valueKey", $classOrClosure)) {
                    array_set("$key.$valueKey", $object, $this->objects);
                } else {
                    throw new \LogicException(sprintf('The object on path "%s" could not be built. The path value is null.', "$key.$valueKey"));
                }
            }

            yield $object;
        }
    })->call($context, $key, $classOrClosure);
}

function register_object_hooks($object)
{
    $resetObjectsHook = function($object, $key) {
        call($object, $key, function($key) {
            array_unset($key, $this->objects);
        });
    };

    $class = get_class($object);
    register_hook($class, 'post_set_value', $resetObjectsHook);
    register_hook($class, 'post_add_value', $resetObjectsHook);

    register_hook($class, 'post_set_values', function($object) {
        call($object, function() {
            $this->objects = [];
        });
    });

    register_hook($class, 'post_build_object', function($object) {
        register_object_hooks($object);
    });

    register_hook($class, 'post_build_sub_object', function($object) {
        register_object_hooks($object);
    });
}

function register_propagate_root_hooks($object)
{
    register_hook($object, 'post_set_object', function ($object, $context, $contextKey) {
        propagate_root($object, $context, $contextKey);
    });

    register_hook($object, 'post_add_object', function ($object, $context, $contextKey) {
        propagate_root($object, $context, $contextKey);
    });

    register_hook($object, 'post_build_sub_object', function ($object, $context, $contextKey) {
        register_propagate_root_hooks($object);
        propagate_root($object, $context, $contextKey);
    });
}

function propagate_root($object, $parentObject, $parentKey)
{
    if (false == $parentObject) {
        return;
    }

    list($rootObject, $rootObjectKey) = call($parentObject, $parentKey, function($parentKey) {
       return [
           isset($this->rootObject) ?: $this,
           isset($this->rootObjectKey) ? $this->rootObjectKey.'.'.$parentKey : $parentKey
       ];
    });

    call($object, $rootObject, $rootObjectKey, function($rootObject, $rootObjectKey) {
        $this->rootObject = $rootObject;
        $this->rootObjectKey = $rootObjectKey;
    });
}
