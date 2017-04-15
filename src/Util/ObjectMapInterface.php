<?php

namespace KikCMS\Util;

use InvalidArgumentException;

/**
 * Map-variant of ObjectList
 *
 * It overlays a key-value representation on top of the ordered list of objects.
 * This map doesn't require keys to be provided, as long as they can be extracted from the object in the specific implementation.
 */
interface ObjectMapInterface extends ObjectListInterface
{
	/**
	 * Add an object to this map
	 *
	 * @param object          $object The object to add
	 * @param null|string|int $key The key to use for this object, if null a key will be derived from the object
	 *
	 * @return self Returns itself to facilitate chaining
	 * @throws InvalidArgumentException If the object doesn't match the 'isValidObject'-method or no key was provided/derived.
	 */
	public function add($object, $key = null);

	/**
	 * Insert an object in this map at a specific position
	 *
	 * Note: This function will reorder numeric keys
	 *
	 * @param object          $object The object to insert
	 * @param int             $position Numeric position to add (0 = beginning); negative values count from the end
	 * @param null|string|int $key The key to use for this object, if null a key will be derived from the object
	 *
	 * @return self Returns itself to facilitate chaining
	 * @throws InvalidArgumentException If the object doesn't match the 'isValidObject'-method or no key was provided/derived.
	 */
	public function insert($object, $position, $key = null);

	/**
	 * Sort the objects by their key
	 *
	 * @param int $sortFlags PHP compatible sort flag, see sort()
	 *
	 * @return self Returns itself to facilitate chaining
	 */
	public function ksort($sortFlags = SORT_REGULAR);

	/**
	 * Sort the objects by key in reverse order
	 *
	 * @param int $sortFlags PHP compatible sort flag, see sort()
	 *
	 * @return self Returns itself to facilitate chaining
	 */
	public function krsort($sortFlags = SORT_REGULAR);
}