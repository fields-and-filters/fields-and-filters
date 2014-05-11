<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('JPATH_PLATFORM') or die;

/**
 * KextensionsArray is an array utility class for doing all sorts of odds and ends with arrays.
 *
 * @since       1.0.0
 */
class KextensionsArray
{
	/**
	 * Flattens a multidimensional array into a single array.
	 *
	 * @param    array $array Array data
	 *
	 * $myArray = array(1,2,3, array(4,5, array(6,7)), array(array(array(8))));
	 * FieldsandfiltersArrayHelper::flatten(myArray); // array(1,2,3,4,5,6,7,8
	 *
	 * @return    array        Flatten array
	 *
	 * @since       1.0.0
	 */
	static function flatten($array)
	{
		if (!is_array($array) || empty($array))
		{
			return $array;
		}

		$flatten = array();

		array_walk_recursive($array, function ($value) use (&$flatten)
		{
			$flatten[] = $value;
		});

		return $flatten;
	}

	/**
	 * Extracts a column from an array of arrays or objects
	 *
	 * @param    array  &$array The source array
	 * @param    string $index  The index of the column or name of object property
	 *
	 * @return    array        Column of values from the source array
	 *
	 * @since       1.0.0
	 */
	public static function getColumn($array, $index, $key = null)
	{
		$result = array();

		if (empty($array) || !(is_array($array) || is_object($array)))
		{
			return $result;
		}

		if (is_object($array))
		{
			$array = get_object_vars($array);
		}

		// $array = array_values( $array );
		// $count = count( $array );

		while ($item = array_shift($array))
		{
			if (is_array($item) && isset($item[$index]))
			{
				if ($key && isset($item[$key]))
				{
					$result[$item[$key]] = $item[$index];
				}
				else
				{
					$result[] = $item[$index];
				}

			}
			elseif (is_object($item) && isset($item->$index))
			{
				if ($key && isset($item->$key))
				{
					$result[$item->$key] = $item->$index;
				}
				else
				{
					$result[] = $item->$index;
				}

			}
			// else ignore the entry
		}

		return $result;
	}

	/**
	 * Check $keys if exists in $array, yes add array to key
	 *
	 * @param    array &$array The source array
	 * @param    array $keys   The array of keys
	 *
	 * @return    array        array merge keys values from $array
	 *
	 * @since       1.0.0
	 */

	public static function fromArray(&$array, $keys)
	{
		$_array = array();

		if (!empty($array) && is_array($array) && is_array($keys))
		{
			$keys  = array_values($keys);
			$count = count($keys);

			for ($x = 0; $x < $count; $x++)
			{
				$key = & $keys[$x];

				if (isset($array[$key]))
				{
					$_array = array_merge($_array, $array[$key]);
				}
			}

			$_array = array_unique($_array);
		}

		return $_array;
	}

	/**
	 * Add array
	 *
	 * @param    array &$source The source array
	 * @param    array $keys    The array of keys
	 * @param    array &$array  The add array
	 *
	 * @since       1.0.0
	 */
	public static function setArrays(&$source, $keys, &$array)
	{
		if (is_array($array) && is_array($keys) && is_array($array))
		{
			$keys  = array_values($keys);
			$count = count($keys);

			for ($x = 0; $x < $count; $x++)
			{
				$key          = & $keys[$x];
				$source[$key] = !isset($source[$key]) ? $array : array_unique(array_merge($source[$key], $array));
			}
		}
	}

	/**
	 * Unset keys from array
	 *
	 * @param    array &$source The source array
	 * @param    array $keys    The array of keys
	 *
	 * @since       1.0.0
	 **/
	public static function unsetKyes($source, $keys)
	{
		if (!empty($source) && is_array($keys))
		{
			$keys  = array_values($keys);
			$count = count($keys);

			for ($x = 0; $x < $count; $x++)
			{
				$key = & $keys[$x];

				if (is_array($source))
				{
					unset($source[$key]);
				}
				elseif (is_object($source))
				{
					unset($source->$key);
				}
			}
		}

		return $source;
	}

	/**
	 * Pivots an array to create a reverse lookup of an array of scalars, arrays or objects.
	 *
	 * @param    array  $source The source array.
	 * @param    string $key    Where the elements of the source array are objects or arrays, the key to pivot on.
	 *
	 * @return    array        An array of arrays pivoted either on the value of the keys, or an individual key of an object or array.
	 *
	 * @since       1.0.0
	 */
	public static function pivot($source, $key = null)
	{
		$result  = array();
		$counter = array();

		foreach ($source as $index => $value)
		{
			// Determine the name of the pivot key, and its value.
			if (is_array($value))
			{
				// If the key does not exist, ignore it.
				if (!isset($value[$key]))
				{
					continue;
				}

				$resultKey   = $value[$key];
				$resultValue = & $source[$index];
			}
			elseif (is_object($value))
			{
				// If the key does not exist, ignore it.
				if (!isset($value->$key))
				{
					continue;
				}

				$resultKey   = $value->$key;
				$resultValue = & $source[$index];
			}
			else
			{
				// Just a scalar value.
				$resultKey   = $value;
				$resultValue = $index;
			}

			if (is_array($resultKey))
			{
				foreach ($resultKey AS $arrayKey)
				{
					// The counter tracks how many times a key has been used.
					if (empty($counter[$arrayKey]))
					{
						// The first time around we just assign the value to the key.
						$result[$arrayKey]  = $resultValue;
						$counter[$arrayKey] = 1;
					}
					elseif ($counter[$arrayKey] == 1)
					{
						// If there is a second time, we convert the value into an array.
						$result[$arrayKey] = array(
							$result[$arrayKey],
							$resultValue,
						);
						$counter[$arrayKey]++;
					}
					else
					{
						// After the second time, no need to track any more. Just append to the existing array.
						$result[$arrayKey][] = $resultValue;
					}

				}
			}
			else
			{
				// The counter tracks how many times a key has been used.
				if (empty($counter[$resultKey]))
				{
					// The first time around we just assign the value to the key.
					$result[$resultKey]  = $resultValue;
					$counter[$resultKey] = 1;
				}
				elseif ($counter[$resultKey] == 1)
				{
					// If there is a second time, we convert the value into an array.
					$result[$resultKey] = array(
						$result[$resultKey],
						$resultValue,
					);
					$counter[$resultKey]++;
				}
				else
				{
					// After the second time, no need to track any more. Just append to the existing array.
					$result[$resultKey][] = $resultValue;
				}
			}

		}

		unset($counter);

		return $result;
	}

	/**
	 * Get empty slot object
	 *
	 * @param    object $object   The source object.
	 * @param    string $ordering The key to test is empty
	 * @param    string $path     Only for JRegistry instanceof
	 *
	 * @return    string    this or next empty key
	 *
	 * @since       1.0.0
	 *
	 */
	static function getEmptySlotObject($object, $ordering, $path = 'form.fields.')
	{
		// if object is instance of JRegistry
		if ($path && $object instanceof JRegistry)
		{
			if ($object->exists($path . $ordering))
			{
				self::getEmptySlotObject($object, ++$ordering, $path);
			}
		}
		// if object is instance of JObject or stdClass
		else
		{
			if (is_object($object))
			{
				if (isset($object->$ordering))
				{
					self::getEmptySlotObject($object, ++$ordering, $path);
				}
			}
		}

		return $ordering;
	}

	/**
	 * Check for Intersect
	 *
	 * @param    array $a First association array
	 * @param    array $b Second array
	 *
	 * @return    boolean
	 *
	 * @since       1.1.0
	 */
	protected static function checkForIntersect($a, $b)
	{
		$c = array_flip($a);

		foreach ($b as $v)
		{
			if (array_key_exists($v, $c))
			{
				return true;
			}
		}

		return false;
	}
}
