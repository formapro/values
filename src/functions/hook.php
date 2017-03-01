<?php
namespace Makasim\Values;

/**
 * @param object   $object
 * @param string   $hook
 * @param \Closure $callback
 */
function register_hook($object, $hook, \Closure $callback)
{
    (function($hook, \Closure $callback) {
        $this->hooks[$hook][] = $callback;
    })->call($object, $hook, $callback);
}

function get_registered_hooks($object, $hook)
{
    return (function($hook) {
        return array_key_exists($hook, $this->hooks) ? $this->hooks[$hook] : [];
    })->call($object, $hook);
}