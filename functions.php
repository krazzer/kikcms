<?php

/**
 * Return the first key of an array
 *
 * @param array $array
 *
 * @return mixed
 */
function first_key(array $array)
{
    return array_keys($array)[0];
}

/**
 * Return the first value of an array
 *
 * @param array $array
 *
 * @return mixed
 */
function first(array $array)
{
    return array_values($array)[0];
}

/**
 * Return the last value of an array
 *
 * @param array $array
 *
 * @return mixed
 */
function last(array $array)
{
    return array_values(array_slice($array, -1))[0];
}


/**
 * Log the given parameters
 */
function dlog()
{
    $args = func_get_args();

    if (count($args) === 1) {
        $args = $args[0];
    }

    error_log(print_r($args, true));
}