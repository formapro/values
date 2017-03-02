<?php
namespace Makasim\Values;

/**
 * @param object|string $objectOrClass
 * @param string        $hook
 * @param \Closure      $callback
 */
function register_hook($objectOrClass, $hook, \Closure $callback)
{
    HookStorage::register($objectOrClass, $hook, $callback);
}

/**
 * @param object $object
 * @param string $hook
 *
 * @return \Closure[]|\Traversable
 */
function get_registered_hooks($object, $hook)
{
    return HookStorage::get($object, $hook);
}
