<?php
namespace Makasim\Values;

/**
 * @param object      $context
 * @param string      $key
 * @param object|null $object
 */
function set_object($context, $key, $object)
{
    (function($key, $object) {
        if ($object) {
            set_value($this, $key, null);
            set_value($this, $key, get_values($object));

            $values =& array_get($key, [], $this->values);
            set_values($object, $values, true);

            array_set($key, $object, $this->objects);
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
    (function($key, $objects) {
        if (null !== $objects) {
            array_set($key, [], $this->objects);

            $objectsValues = [];
            foreach ($objects as $objectKey => $object) {
                array_set($objectKey, get_values($object), $objectsValues);
            }

            set_value($this, $key, $objectsValues);

            foreach ($objects as $objectKey => $object) {
                $values =& array_get($key.'.'.$objectKey, [], $this->values);
                set_values($object, $values, true);

                array_set($key.'.'.$objectKey, $object, $this->objects);
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
    (function($key, $object, $objectKey) {
        $objectValues = get_values($object);

        $objectKey = add_value($this, $key, $objectValues, $objectKey);

        $values =& array_get($key.'.'.$objectKey, [], $this->values);
        set_values($object, $values, true);

        array_set($key.'.'.$objectKey, $object, $this->objects);

    })->call($context, $key, $object, $objectKey);
}

/**
 * @param string $key
 * @param $classOrClosure
 *
 * @return object|null
 */
function get_object($object, $key, $classOrClosure)
{
    return (function($key, $classOrClosure) {
        if (false == $object = array_get($key, null, $this->objects)) {
            $values =& array_get($key, null, $this->values);
            if (null === $values) {
                return;
            }

            $object = build_object($classOrClosure, $values, $this, $key);

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
                $values =& array_get("$key.$valueKey", [], $this->values);

                $object = build_object($classOrClosure, $values, $this, $key);

                array_set("$key.$valueKey", $object, $this->objects);
            }

            yield $object;
        }
    })->call($context, $key, $classOrClosure);
}
