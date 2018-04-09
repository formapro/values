<?php
namespace Makasim\Values;

function &array_get($key, $default, &$values)
{
    if (preg_match('/\w\d\./', $key)) {
        throw new \LogicException(sprintf('The key must contain only a-Z0-9 and "." symbols. Got "%s', $key));
    }

    $path = str_replace('.', '\'][\'', $key);

    $result = null;
    eval('$result &= isset($values[\''.$path.'\']) ? $values[\''.$path.'\'] : $default;');

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
    if (preg_match('/\w\d\./', $key)) {
        throw new \LogicException(sprintf('The key must contain only a-Z0-9 and "." symbols. Got "%s', $key));
    }

    $path = str_replace('.', '\'][\'', $key);

    $previousValue = null;
    eval('
        $previousValue = isset( $values[\''.$path.'\']) ? $values[\''.$path.'\'] : null;
        $values[\''.$path.'\'] = $value;
    ');

    return $previousValue !== $value;
}

function array_has($key, array &$values)
{
    if (preg_match('/\w\d\./', $key)) {
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
    if (preg_match('/\w\d\./', $key)) {
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
