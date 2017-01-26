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
 * Get current time in milliseconds
 *
 * @return float
 */
function msec()
{
    list($usec, $sec) = explode(" ", microtime());
    $mSec = ((float) $usec / 1000) + (float) $sec;

    return $mSec * 1000;
}