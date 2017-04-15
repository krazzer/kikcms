<?php

namespace KikCMS\Util;

use InvalidArgumentException;

/**
 * Object for storing a ordered group of objects.
 *
 * This interface mimics PHP's array-behavior with numeric keys, but adds functionality and allows for (more) defensive coding.
 */
interface ObjectListInterface extends \Countable, \Iterator
{
	/**
	 * Remove all elements from this list
	 *
	 * @return self Returns itself to facilitate chaining
	 */
	public function clear();

	/**
	 * Checks if current pointer position points to an actual element
	 *
	 * @return bool True if current pointer position is valid (not past end, not empty), otherwise false
	 */
	public function valid();

	/**
	 * Returns key for the object for the 'current' position of the pointer
	 *
	 * @return string|int|null Key of current object or null
	 */
	public function key();

	/**
	 * Returns all keys
	 *
	 * @return string[]|int[] A list of all the keys
	 */
	public function keys();

	/**
	 * Returns object for the 'current' position of the pointer
	 *
	 * @return object|false Current object or false
	 */
	public function current();

	/**
	 * Moves pointer forward to the next object and returns that object
	 *
	 * @return object|false Next object or false
	 */
	public function next();

	/**
	 * Rewinds pointer to the first object
	 *
	 * @return self Returns itself to facilitate chaining
	 */
	public function rewind();

	/**
	 * Checks if this list is empty
	 *
	 * @return bool True if list is empty, otherwise false
	 */
	public function isEmpty();

	/**
	 * Returns the amount of objects in list
	 *
	 * @return int Amount of objects in list
	 */
	public function count();

	/**
	 * Checks if the 'current' position of the pointer points to the first object
	 *
	 * @return bool True if pointer is on first object, otherwise false
	 */
	public function isOnFirst();

	/**
	 * Checks if 'current' position of the pointer points to the last object
	 *
	 * @return bool True if pointer is on last object, otherwise false
	 */
	public function isOnLast();

	/**
	 * Checks if the current position of the pointer is the specified position
	 *
	 * @param int $position The position to check (Use negative numbers to start from the end of the list)
	 *
	 * @return bool True if pointer is on given position, otherwise false
	 * @throws InvalidArgumentException If the given position couldn't be understood
	 */
	public function isOnPosition($position);

	/**
	 * Returns the first object
	 *
	 * @return object|false First object or false if empty
	 */
	public function getFirst();

	/**
	 * Returns the last object
	 *
	 * @return object|false Last object or false if empty
	 */
	public function getLast();

	/**
	 * Returns the object at the given position
	 *
	 * @param int $position The position to fetch (Use negative numbers to start from end of list)
	 *
	 * @return object|false Object from given position or false if not found
	 * @throws InvalidArgumentException If the given position couldn't be understood
	 */
	public function getForPosition($position);

	/**
	 * Checks if the requested key exists in this list
	 *
	 * @param string|int $key
	 *
	 * @return bool True if this list contains the requested key, otherwise false
	 */
	public function has($key);

	/**
	 * Checks if the requested object exists in this list
	 *
	 * @param object $object
	 *
	 * @return string|int|false The key if object was found, otherwise false
	 */
	public function hasObject($object);

	/**
	 * Returns the object for the given key
	 *
	 * @param string|int $key
	 *
	 * @return object|false The requested object if found, otherwise false
	 */
	public function get($key);

	/**
	 * Returns all objects in this list
	 *
	 * @return object[]
	 */
	public function getObjects();

	/**
	 * Add an object to this list
	 *
	 * @param object $object
	 *
	 * @return self Returns itself to facilitate chaining
	 * @throws InvalidArgumentException
	 */
	public function add($object);

	/**
	 * Insert an object in this list at a specific position
	 *
	 * Note: This function will free the provided position by moving objects (starting from position) further 'back'. In doing so, it may renumber numeric keys.
	 *
	 * @param object $object
	 * @param int    $position Numeric position to add (0 = beginning); negative values count from the end,
	 * 							insertion happens <i>before</i> that negative position (i.e. -1 inserts before the last element)
	 *
	 * @return self Returns itself to facilitate chaining
	 * @throws InvalidArgumentException If the given position cannot be understood
	 */
	public function insert($object, $position);

	/**
	 * Remove an object from this list
	 *
	 * Note: This function may renumber numeric keys
	 *
	 * @param string|int $key The key of the object to remove
	 *
	 * @return self Returns itself to facilitate chaining
	 */
	public function remove($key);

	/**
	 * Remove all instances of the provided object from this list
	 *
	 * Note: This function may reorder numeric keys
	 * Note: This only removes exact copies of the provided object
	 *
	 * @param object $object The object to remove
	 *
	 * @return self Returns itself to facilitate chaining
	 */
	public function removeObject($object);

	/**
	 * Reverses the order of the objects in this list
	 *
	 * @return self Returns itself to facilitate chaining
	 */
	public function reverse();
}