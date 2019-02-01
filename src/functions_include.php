<?php

// Don't redefine the functions if included multiple times.

if (false == function_exists('Formapro\Values\get_values')) {
    require __DIR__ . '/functions/values.php';
}
if (false == function_exists('Formapro\Values\array_get')) {
    require __DIR__ . '/functions/array.php';
}
if (false == function_exists('Formapro\Values\register_hook')) {
    require __DIR__ . '/functions/hook.php';
}
if (false == function_exists('Formapro\Values\get_object')) {
    require __DIR__ . '/functions/objects.php';
}
