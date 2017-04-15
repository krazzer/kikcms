<?php

namespace KikCMS\Util;

use InvalidArgumentException;

/**
 * Default implementation of ObjectMapInterface
 *
 * This simply overlays a key-value representation on top of the ordered list of objects.
 * This map can extract keys from objects via getKeyForObject(), so a key is not required upon add() or insert().
 * Note: The default implementation only supports retrieving the id of a Identifiable, any other object will require a provided key.
 */
class ObjectMap extends ObjectList implements ObjectMapInterface
{
	/**
	 * Keys array.
	 *
	 * The objects-array of the ObjectList is reused in this implementation to facility key->value mapping.
	 * To support the tracking of 'insert order' and inserting at a specific position, this $keys-array is kept to facilitate both order-tracking and fast 'position'-lookups.
	 *
	 * I.e. this is basically a position -> key map, so a position-based lookup is simply:
	 * $key = $keys[$pos];
	 * $value = $objects[$key];
	 *
	 * @var int[]|string[]
	 */
	protected $keys = [];

	/** @inheritdoc */
	public function clear()
	{
		// Clear the keys
		$this->keys = [];

		// Clear the objects
		return parent::clear();
	}

	/** @inheritdoc */
	public function key()
	{
		// Get the pointer of the keys-array and return the actual key represented by that position.
		// Note: this is similar to current(), but that method doesn't return null when the pointer is 'invalid'.
		$index = key($this->keys);

		return ($index !== null)
			? $this->keys[$index]
			: null;
	}

	/** @inheritdoc */
	public function keys()
	{
		return $this->keys;
	}

	/** @inheritdoc */
	public function current()
	{
		$key = current($this->keys);

		return ($key !== false)
			? $this->objects[$key]
			: false;
	}

	/** @inheritdoc */
	public function next()
	{
		$key = next($this->keys);

		return ($key !== false)
			? $this->objects[$key]
			: false;
	}

	/** @inheritdoc */
	public function rewind()
	{
		reset($this->keys);

		return $this;
	}

	/** @inheritdoc */
	public function isOnPosition($position)
	{
		// Get index
		$index = $this->getIndexForPosition($position);

		// Check index
		if ( ! isset($this->keys[$index]))
		{
			return false;
		}

		return $this->key() === $this->keys[$index];
	}

	/** @inheritdoc */
	public function getForPosition($position)
	{
		// Get index
		$index = $this->getIndexForPosition($position);

		// Check index
		if ( ! isset($this->keys[$index]))
		{
			return false;
		}

		// Get key
		$key = $this->keys[$index];

		return $this->get($key);
	}

	/** @inheritdoc */
	public function hasObject($object)
	{
		// Get key
		$key = array_search($object, $this->objects, true);

		if ($key !== false)
		{
			return $key;
		}

		// Try using default key of object
		$key = $this->getKeyForObject($object);

		return ($key !== null && $this->has($key) && $this->get($key) instanceof $object) ? $key : false;
	}

	/** @inheritdoc */
	public function add($object, $key = null)
	{
		// Just use addObjectToList, but return 'this' rather than the final key.
		$this->addObjectToList($object, $key);

		return $this;
	}

	/** @inheritdoc */
	public function insert($object, $position, $key = null)
	{
		if ( ! is_int($position))
		{
			throw new InvalidArgumentException('Expected int for $position');
		}

		// Keep track of the original count, before adding elements
		$count = $this->count();

		// Add object and key as normal
		$key = $this->addObjectToList($object, $key);

		// But move the key to the required position
		// Retrieve current position of the key
		$index = array_search($key, $this->keys, true);

		// Prevent duplication of the specific key
		unset($this->keys[$index]);

		if ($position === 0 || $position < -$count)
		{
			// Prepend the key to start of the list of keys (Will renumber key positions)
			array_unshift($this->keys, $key);
		}
		else
		{
			// Calculate correct offset
			$offset = $this->getIndexForPosition($position);

			// If the position was negative it is skewed one position since the element was already added.
			if($position < 0)
				$offset--;

			// Add key to specific position (Will renumber key positions)
			array_splice($this->keys, $offset, 0, [$key]);
		}

		return $this;
	}

	/** @inheritdoc */
	public function remove($key)
	{
		// Get index
		$index = array_search($key, $this->keys, true);

		if ($index === false)
		{
			return $this;
		}

		// Remove key
		unset($this->keys[$index]);

		// Remove object
		unset($this->objects[$key]);

		// Renumber key positions
		$this->keys = array_values($this->keys);

		return $this;
	}

	/** @inheritdoc */
	public function reverse()
	{
		$this->keys = array_reverse($this->keys);

		return $this;
	}

	/** @inheritdoc */
	public function ksort($sortFlags = SORT_REGULAR)
	{
		// Flip and sort keys
		$keys = array_flip($this->keys);
		ksort($keys, $sortFlags);

		// Renumber key positions
		$this->keys = array_keys($keys);

		return $this;
	}

	/** @inheritdoc */
	public function krsort($sortFlags = SORT_REGULAR)
	{
		// Flip and sort keys
		$keys = array_flip($this->keys);
		krsort($keys, $sortFlags);

		// Renumber key positions
		$this->keys = array_keys($keys);

		return $this;
	}

	/**
	 * Returns the first key
	 *
	 * @return int|string
	 */
	public function getFirstKey()
	{
		return $this->keys[0];
	}

	/**
	 * Returns default key for given object
	 *
	 * @param object $object
	 *
	 * @return int|string|null The key if possible, otherwise null
	 */
	protected function getKeyForObject($object)
	{
		return ($object instanceof Identifiable)
			? $object->getId()
			: null;
	}

	/**
	 * Actually add an object to the list of objects, generating a key if necessary.
	 *
	 * @param object $object
	 * @param int|string|null $key
	 * @return int|string The actual key.
	 * @throws InvalidArgumentException If the object doesn't match the 'isValidObject'-method or no key was provided/derived.
	 */
	protected function addObjectToList($object, $key)
	{
		if ( ! $this->isValidObject($object))
		{
			throw new InvalidArgumentException('Invalid object');
		}

		if($key === null)
		{
			// Get default key of object
			$key = $this->getKeyForObject($object);

			if($key === null)
			{
				throw new InvalidArgumentException('No key provided and unable to get key from object');
			}
		}

		// Check if object already exists
		if( ! isset($this->objects[$key]))
		{
			// Add key to list
			$this->keys[] = $key;
		}

		// Add (replace) object to list
		$this->objects[$key] = $object;

		return $key;
	}
}