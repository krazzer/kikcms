<?php

/**
 * Add a value to an array after a certain key
 *
 * @param array $subject
 * @param string $keyToAddAfter
 * @param string $keyToAdd
 * @param $valueToAdd
 * @return array
 */
function array_add_after_key(array $subject, string $keyToAddAfter, string $keyToAdd, $valueToAdd): array
{
    $index = array_search($keyToAddAfter, array_keys($subject));

    if($index === false){
        $index = count($subject);
    } else {
        $index++;
    }

    $newArrayPart1 = array_slice($subject, 0, $index, true);
    $newArrayPart2 = array_slice($subject, $index, null, true);

    $newArrayPart1[$keyToAdd] = $valueToAdd;

    return $newArrayPart1 + $newArrayPart2;
}

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

// Make phalcon APC module work with APCu, Remove this when migrating to Redis
function apc_add($key, $var, $ttl)
{
    return apcu_add($key, $var, $ttl);
}

function apc_cas($key, $old, $new)
{
    return apcu_cas($key, $old, $new);
}

function apc_clear_cache()
{
    return apcu_clear_cache();
}

function apc_dec($key, $step, $success)
{
    return apcu_dec($key, $step, $success);
}

function apc_delete($key)
{
    return apcu_delete($key);
}

function apc_entry($key, $generator, $ttl)
{
    return apcu_entry($key, $generator, $ttl);
}

function apc_exists($keys)
{
    return apcu_exists($keys);
}

function apc_fetch($key)
{
    return apcu_fetch($key);
}

function apc_inc($key, $step, $success)
{
    return apcu_inc($key, $step, $success);
}

function apc_sma_info($lim)
{
    return apcu_sma_info($lim);
}

function apc_store($key, $var, $ttl)
{
    return apcu_store($key, $var, $ttl);
}

class APCIterator extends APCuIterator
{
    public function __construct($cache, $search = null, $format = APC_ITER_ALL, $chunk_size = 100, $list = APC_LIST_ACTIVE)
    {
        parent::__construct($search, $format, $chunk_size, $list);
    }
}