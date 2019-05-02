<?php
namespace Formapro\Values;

function &array_get($key, $default, &$values)
{
    if (false == preg_match('/([\d\w]*)\.?/', $key)) {
        throw new \LogicException(sprintf('The key must contain only a-Z0-9 and "." symbols. Got "%s"', $key));
    }

    $path = str_replace('.', '\'][\'', $key);

    $result = null;
    eval('
        if (isset($values[\''.$path.'\'])) {
            $result =& $values[\''.$path.'\']; 
        } else {
            $result = $default;
        }
    ');

    return $result;
}

/**
 * @param string $key
 * @param mixed $value
 * @param array $values
 *
 * @return bool return true if a modification to data was done, false if nothing is changed
 */
function array_set($key, $value, array &$values)
{
    $keys = explode('.', $key);

    array_path_set($values, $keys, $value);

    return true;
}

function array_has($key, array &$values)
{
    if (false == preg_match('/([\d\w]*)\.?/', $key)) {
        throw new \LogicException(sprintf('The key must contain only a-Z0-9 and "." symbols. Got "%s', $key));
    }

    $path = str_replace('.', '\'][\'', $key);

    $result = false;
    eval('$result = isset($values[\''.$path.'\']);');

    return $result;
}

/**
 * @param $key
 * @param array $values
 *
 * @return bool Returns true if data was changed, false if it is unchanged.
 */
function array_unset($key, array &$values)
{
    if (false == preg_match('/([\d\w]*)\.?/', $key)) {
        throw new \LogicException(sprintf('The key must contain only a-Z0-9 and "." symbols. Got "%s', $key));
    }

    $path = str_replace('.', '\'][\'', $key);

    $result = false;
    eval('$result = isset($values[\''.$path.'\']);');
    eval('unset($values[\''.$path.'\']);');

    return $result;
}

function array_copy(array $array)
{
    // values array may contain sub array passed as a reference to a sub object.
    // this code removes such refs from the array.
    // Here's "foreach rec optimized" version which showed the best result
    // performance results (1000 cycles):
    //   get_values              - 0.001758
    //   foreach rec optimized   - 0.008587
    //   foreach recursion       - 0.015547
    //   serialize\unserialze    - 0.020816
    //   json encode\decode      - 0.078953
    $copiedArray = [];
    foreach($array as $key => $value) {
        if(is_array($value)) {
            $value = array_copy($value);
        }

        $copiedArray[$key] = $value;
    }

    return $copiedArray;
}

/**
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21NestedArray.php/function/NestedArray%3A%3AsetValue/8
 */
function array_path_set(array &$array, array $keys, $value, $force = false) {
    $ref = &$array;
    foreach ($keys as $parent) {
        // PHP auto-creates container arrays and NULL entries without error if $ref
        // is NULL, but throws an error if $ref is set, but not an array.
        if ($force && isset($ref) && !is_array($ref)) {
            $ref = array();
        }
        $ref = &$ref[$parent];
        if (!is_array($ref)) {
            $ref = [];
        }
    }
    $ref = $value;
}
