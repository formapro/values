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
        $resetChangedValuesHook = function() {
            $this->changedValues = [];


        };

        $trackChangesHook = function($object, $key, $value, $modified) {
            if (false == $modified) {
                return $value;
            }

            if (null !== $value) {
                array_set($key, $value, $this->changedValues);
            } elseif ($modified) {
                array_set($key, null, $this->changedValues);
            }

            return $value;
        };

//        $trackSetObjectChangesHook = function($context, $key, $object, $modified) {
//            if ($object) {
//                array_set($key, get_values($object), $this->changedValues);
//            } else {
//                if ($modified) {
//                    array_set($key, null, $this->changedValues);
//                }
//            }
//        };
//
//        $trackSetObjectsChangesHook = function($context, $key, $objects, $modified) {
//            if ($objects) {
//                foreach ($objects as $objectKey => $object) {
//                    array_set($key.'.'.$objectKey, get_values($object), $this->changedValues);
//                }
//            } else {
//                if ($modified) {
//                    array_set($key, null, $this->changedValues);
//                }
//            }
//        };

        register_hook($this, 'post_set_values', $resetChangedValuesHook);
        register_hook($this, 'post_set_value', $trackChangesHook);
        register_hook($this, 'post_add_value', $trackChangesHook);
//        register_hook($this, 'post_set_object', $trackSetObjectChangesHook);
//        register_hook($this, 'post_set_objects', $trackSetObjectsChangesHook);
    }
}
