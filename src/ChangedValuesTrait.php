<?php
namespace Formapro\Values;

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

        register_hook(get_class($this), HooksEnum::POST_SET_VALUES, $resetChangedValuesHook);
        register_hook(get_class($this), HooksEnum::POST_SET_VALUE, $trackChangesHook);
        register_hook(get_class($this), HooksEnum::POST_ADD_VALUE, $trackChangesHook);
    }
}
