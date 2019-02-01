<?php
namespace Formapro\Values;

/**
 * @property array $values
 * @property array $changedValues
 */
trait ObjectsTrait
{
    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @var object|null
     */
    protected $rootObject;

    /**
     * @var string|null
     */
    protected $rootObjectKey;

    /**
     * @param string               $key
     * @param string|\Closure|null $classOrClosure
     *
     * @return object|null
     */
    protected function getObject($key, $classOrClosure = null)
    {
        return get_object($this, $key, $classOrClosure);
    }

    /**
     * @param string      $key
     * @param object|null $object
     */
    protected function setObject($key, $object)
    {
        set_object($this, $key, $object);
    }

    /**
     * @param string   $key
     * @param object[] $objects
     */
    protected function setObjects($key, $objects)
    {
        set_objects($this, $key, $objects);
    }

    /**
     * @param string $key
     * @param object $object
     * @param string|null $objectKey
     */
    protected function addObject($key, $object, $objectKey = null)
    {
        add_object($this, $key, $object, $objectKey);
    }

    /**
     * @param string               $key
     * @param string|\Closure|null $classOrClosure
     *
     * @return \Traversable
     */
    protected function getObjects($key, $classOrClosure = null)
    {
        return get_objects($this, $key, $classOrClosure);
    }
}