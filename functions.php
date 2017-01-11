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

