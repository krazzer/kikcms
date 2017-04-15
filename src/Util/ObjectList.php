<?php

namespace KikCMS\Util;

use InvalidArgumentException;

/**
 * Default implementation of ObjectListInterface.
 *
 * It allows validation of objects via validateObject(). The default implementation only tests for items actually being a object.
 */
class ObjectList implements ObjectListInterface
{
	/**
	 * The list of objects
	 *
	 * @var object[]
	 */
	protected $objects = [];

	/**
	 * ObjectList constructor.
	 *
	 * @param object[] $objects
	 */
	public function __construct($objects = [])
	{
		foreach ($objects as $object)
		{
			$this->add($object);
		}
	}

	/** @inheritdoc */
	public function clear()
	{
		$this->objects = [];

		return $this;
	}

	/** @inheritdoc */
	public function valid()
	{
		return ($this->key() !== null);
	}

	/** @inheritdoc */
	public function key()
	{
		return key($this->objects);
	}

	/** @inheritdoc */
	public function keys()
	{
		return array_keys($this->objects);
	}

	/** @inheritdoc */
	public function current()
	{
		return current($this->objects);
	}

	/** @inheritdoc */
	public function next()
	{
		return next($this->objects);
	}

	/** @inheritdoc */
	public function rewind()
	{
		reset($this->objects);

		return $this;
	}

	/** @inheritdoc */
	public function isEmpty()
	{
		return ($this->count() === 0);
	}

	/** @inheritdoc */
	public function count()
	{
		return count($this->objects);
	}

	/** @inheritdoc */
	public function isOnFirst()
	{
		return $this->isOnPosition(0);
	}

	/** @inheritdoc */
	public function isOnLast()
	{
		return $this->isOnPosition(-1);
	}

	/** @inheritdoc */
	public function isOnPosition($position)
	{
		return $this->key() === $this->getIndexForPosition($position);
	}

	/** @inheritdoc */
	public function getFirst()
	{
		return $this->getForPosition(0);
	}

	/** @inheritdoc */
	public function getLast()
	{
		return $this->getForPosition(-1);
	}

	/** @inheritdoc */
	public function getForPosition($position)
	{
		// Get index
		$index = $this->getIndexForPosition($position);

		return $this->get($index);
	}

	/** @inheritdoc */
	public function has($key)
	{
		return isset($this->objects[$key]);
	}

	/** @inheritdoc */
	public function hasObject($object)
	{
		return array_search($object, $this->objects, true);
	}

	/** @inheritdoc */
	public function get($key)
	{
		return $this->has($key) ? $this->objects[$key] : false;
	}

	/** @inheritdoc */
	public function getObjects()
	{
		return $this->objects;
	}

	/** @inheritdoc */
	public function add($object)
	{
		if ( ! $this->isValidObject($object))
		{
			throw new InvalidArgumentException('Invalid object');
		}

		// Add object to list
		$this->objects[] = $object;

		return $this;
	}

	/** @inheritdoc */
	public function insert($object, $position)
	{
		if ( ! $this->isValidObject($object))
		{
			throw new InvalidArgumentException('Invalid object');
		}

		if ( ! is_int($position))
		{
			throw new InvalidArgumentException('Expected int for $position');
		}

		if ($position === 0 || $position < -$this->count())
		{
			// Prepend object to the start of the list (Will renumber keys)
			array_unshift($this->objects, $object);
		}
		else
		{
			// Calculate correct offset
			$offset = $this->getIndexForPosition($position);

			// Insert object to specific position (Will renumber keys)
			array_splice($this->objects, $offset, 0, [$object]);
		}

		return $this;
	}

	/** @inheritdoc */
	public function remove($key)
	{
		// Check key
		if ( ! isset($this->objects[$key]))
		{
			return $this;
		}

		// Remove object
		unset($this->objects[$key]);

		// Renumber keys
		$this->objects = array_values($this->objects);

		return $this;
	}

	/** @inheritdoc */
	public function removeObject($object)
	{
		// Remove all matching objects
		while (($key = array_search($object, $this->objects, true)) !== false)
		{
			$this->remove($key);
		}

		return $this;
	}

	/** @inheritdoc */
	public function reverse()
	{
		$this->objects = array_reverse($this->objects);

		return $this;
	}

	/**
	 * Calculates index for given position
	 *
	 * @param int $position
	 *
	 * @return int
	 * @throws InvalidArgumentException
	 */
	protected function getIndexForPosition($position)
	{
		if ( ! is_int($position))
		{
			throw new InvalidArgumentException('Expected int for $position');
		}

		return ($position < 0)
			? $this->count() + $position
			: $position;
	}

	/**
	 * Checks if object is valid for this list
	 *
	 * @param object $object
	 *
	 * @return bool True if object is valid for this list, otherwise false
	 */
	protected function isValidObject($object)
	{
		return is_object($object);
	}
}