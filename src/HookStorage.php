<?php
namespace Makasim\Values;

final class HookStorage
{
    /**
     * @var \Closure[]
     */
    private static $hooks = [];

    public static function clearAll()
    {
        self::$hooks = [];
    }

    public static function getAll()
    {
        return self::$hooks;
    }

    /**
     * @param object|string $objectOrClass
     * @param string        $hook
     * @param \Closure      $callback
     */
    public static function register($objectOrClass, $hook, \Closure $callback)
    {
        $hookId = is_object($objectOrClass) ? self::getHookId($objectOrClass) : (string) $objectOrClass;

        self::$hooks[$hook][$hookId][spl_object_hash($callback)] = $callback;
    }

    /**
     * @param object $object
     * @param string $hook
     *
     * @return \Traversable|\Closure[]
     */
    public static function get($object, $hook)
    {
        foreach (self::$hooks[$hook][self::getHookId($object)] ?? [] as $callback) {
            yield $callback;
        }

        foreach (self::$hooks[$hook][get_class($object)] ?? [] as $callback) {
            yield $callback;
        }
    }

    /**
     * @param object $object
     *
     * @return string
     */
    public static function getHookId($object)
    {
        return (function() {
            if (false == $this->hookId) {
                $this->hookId = get_class($this).':'.uniqid('', true);
            }

            return $this->hookId;
        })->call($object);
    }

    private function __construct()
    {
    }
}