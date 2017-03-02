<?php
namespace Makasim\Values;

trait ChangedValuesTrait
{
    /**
     * @var array
     */
    protected $changedValues = [];

    protected function registerChangedValuesHooks()
    {
        $resetChangedValuesHook = function($object) {
            $object->changedValues = [];
        };

        $trackChangesHook = function($object, $key, $value, $modified) {
            if (false == $modified) {
                return $value;
            }

            if (null !== $value) {
                array_set($key, $value, $object->changedValues);
            } elseif ($modified) {
                array_set($key, null, $object->changedValues);
            }

            return $value;
        };

        register_hook(get_class($this), 'post_set_values', $resetChangedValuesHook);
        register_hook(get_class($this), 'post_set_value', $trackChangesHook);
        register_hook(get_class($this), 'post_add_value', $trackChangesHook);
    }
}
