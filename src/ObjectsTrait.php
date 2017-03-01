<?php
namespace Makasim\Values;

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
     * @var \Closure|null
     */
    protected $objectBuilder;

    protected function  registerObjectsHooks()
    {
        $resetObjectsHook = function($object, $key) {
            array_unset($key, $this->objects);
        };
        $resetAllObjectsHook = function() {
            $this->objects = [];
        };

        register_hook($this, 'post_set_value', $resetObjectsHook);
        register_hook($this, 'post_add_value', $resetObjectsHook);
        register_hook($this, 'post_set_values', $resetAllObjectsHook);
    }

    /**
     * @param string $key
     * @param $classOrClosure
     *
     * @return object|null
     */
    protected function getObject($key, $classOrClosure)
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
     * @param string          $key
     * @param string|\Closure $classOrClosure
     *
     * @return \Traversable
     */
    protected function getObjects($key, $classOrClosure)
    {
        return get_objects($this, $key, $classOrClosure);
    }

    public function __clone()
    {
        if ($this->objectBuilder) {
            $this->objectBuilder = \Closure::bind($this->objectBuilder, $this);
        }
    }
}