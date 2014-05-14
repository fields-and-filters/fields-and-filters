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
 * KextensionsBufferCore.
 *
 * @since       1.0.0
 */
abstract class KextensionsBufferCore
{
	/**
	 * The elements instance.
	 *
	 * @var    $_instances
	 * @since       1.0.0
	 */
	protected static $_instances = array();

	/**
	 * Database Connector
	 *
	 * @var    object
	 * @since       1.0.0
	 */
	protected $_db;

	/**
	 * All cache object elements and values
	 *
	 * @var    object.
	 * @since       1.0.0
	 */
	protected $_data;

	/**
	 * Cache column elements and values
	 *
	 * @var    object.
	 * @since       1.0.0
	 */
	protected $_columns = array();

	/**
	 * Cache pivot elements and values
	 *
	 * @var    object.
	 * @since       1.0.0
	 */
	protected $_pivots = array();

	/**
	 * An array of names that don't exists
	 *
	 * @var    array
	 * @since       1.0.0
	 */
	protected $_not;

	/**
	 * Cache name methods
	 *
	 * @var    object.
	 * @since       1.0.0
	 */
	protected $_methods = array();

	/**
	 * Temp varibles elements and values when method in running
	 *
	 * @var    object.
	 * @since       1.0.0
	 */
	protected $buffer;

	/**
	 * Cache name method
	 *
	 * @var    object.
	 * @since       1.0.0
	 */
	protected $method;

	/**
	 * The array types
	 *
	 * @var    array
	 * @since       1.0.0
	 */
	protected $types = array();

	/**
	 * The array/null of elements or items id
	 *
	 * @var    null/array
	 * @since       1.0.0
	 */
	protected $elements = array();

	protected $notElements = array();

	/**
	 * The array/null of states
	 *
	 * @var    null/array
	 * @since       1.0.0
	 */
	protected $states = array();

	/**
	 * The array configuration
	 *
	 * @var    array
	 * @since       1.0.0
	 */
	protected $config;

	/**
	 * The information on whether the reset variables
	 *
	 * @var    boolean
	 * @since       1.0.0
	 */
	protected $reset = true;

	/**
	 * Constructor
	 *
	 * @since       1.0.0
	 */
	public function __construct($debug = null)
	{
		// Instantiate the internal object.
		$this->_db    = JFactory::getDbo();
		$this->_data  = new stdClass;
		$this->buffer = new JObject;
		$this->config = new JObject;
	}

	/**
	 * Returns a reference to the global KextensionsBuffer object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $kes = KextensionsBuffer::getInstance();
	 *
	 * @return  KextensionsBuffer Instance
	 *
	 * @since       1.0.0
	 */
	public static function getInstance($debug = false)
	{
		$name = get_called_class();

		// Only create the object if it doesn't exist.
		if (empty(self::$_instances[$name]))
		{
			self::$_instances[$name] = new $name($debug);
		}

		return self::$_instances[$name];
	}

	/**
	 * Method to get date of cache
	 *
	 * @param    int $extension_type_id extension type id
	 *
	 * @return    instance of date type
	 *
	 * @since       1.0.0
	 */
	protected function &_getData($name)
	{
		/**
		 * Create new cache array filters
		 * objects    elements    object of elements
		 * array    __state        witch state you get when you get full elements from type
		 */
		if (!isset($this->_data->$name))
		{
			$data = $this->_data->$name = new JObject(array('elements' => new JObject, '__states' => array()));
			if (is_array($this->_not))
			{
				$data->setProperties(array_fill_keys(array_values($this->_not), array()));
			}
		}

		return $this->_data->$name;
	}

	/**
	 * Check arguments
	 *
	 * @param    int /array/string        $types                    types ( e.g., extension_type_id )
	 * @param    int /array/string        $elements        elements ( e.g., element_id, item_id, field_id  )
	 * @param    int /array/null        $states                states
	 *
	 * @return  boolean
	 *
	 * @since       1.0.0
	 **/
	protected function _checkArgs($types, $elements = null, $states = null)
	{
		// Preparation type
		$this->_checkArg($types, 'Types');

		if (empty($types))
		{
			return false;
		}

		$this->types = $types;
		reset($this->types);

		if (!is_null($elements))
		{
			// Preparation elements
			$this->_checkArg($elements, 'Elements');

			if (empty($elements))
			{
				return false;
			}

			$this->elements = $elements;
			reset($this->elements);
		}

		// Preparation states. Only unique and intiger ids
		$states = is_null($states) ? array(1) : array_unique((array) $states);
		JArrayHelper::toInteger($states);

		$this->states = $states;

		return true;
	}

	/**
	 * Check siingle argument
	 *
	 * @param    array  &$arg check if elements in array are the string or numeric
	 * @param    stirng $name name of type or element
	 *
	 * @since       1.0.0
	 **/
	protected function _checkArg(&$arg, $name)
	{
		$arg = array_unique((array) $arg);

		// Check array type elements and add this information to configuration
		if (!$this->config->get('string' . $name))
		{
			JArrayHelper::toInteger($arg);

			$this->config->set('numeric' . $name, true);
		}

		reset($arg);
	}

	/**
	 * Add new elements to don't exists elements
	 *
	 * @param    array  $elements elements ( e.g., element_id, item_id, field_id  )
	 * @param    string &$notType key name in array $this->_not
	 *
	 * @since       1.0.0
	 **/
	protected function _setNot($elements, $notName = 'elements')
	{
		if (!empty($elements))
		{
			$notType = isset($this->_not[$notName]) ? $this->_not[$notName] : $this->_not[key($this->_not)];

			reset($this->types);
			while (($type = current($this->types)) !== false)
			{
				// Get type date
				$data = $this->_getData($type);

				// Get not Elements and set new elements
				$_not = $data->get($notType, array());
				KextensionsArray::setArrays($_not, $this->states, $elements);
				$data->set($notType, $_not);

				next($this->types);
			}

			unset($data, $elements, $_not);
		}
	}

	/**
	 * Remoweve all elements of states from don't exists elements
	 *
	 * @param          int     /string        type        types ( e.g., extension_type_id )
	 * @param    array $states states
	 *
	 * @since       1.0.0
	 **/
	protected function _unsetNot($type, $states)
	{
		// Get type date
		$data = $this->_getData($type);

		reset($this->_not);
		while (($not = current($this->_not)) !== false)
		{
			// Get not Elements buffer from date
			$notBuffer = $data->get($not, array());

			if (!empty($dataNot))
			{
				// unset elements from form cahce date
				$data->set($not, KextensionsArray::unsetKyes($notBuffer, $states));
			}

			next($this->_not);
		}
	}

	/**
	 * Reset arguments
	 *
	 * @param    boolean $reset reset arguments if you need
	 *
	 * @return      boolean         if is reset
	 * @since       1.0.0
	 **/
	protected function _resetArgs($reset = null)
	{
		$reset = !is_null($reset) ? (boolean) $reset : $this->reset;

		if ($reset)
		{
			$this->types       = array();
			$this->elements    = array();
			$this->notElements = array();
			$this->states      = array();
			$this->reset       = true;
			$this->method      = null;
			$this->config      = new JObject;
			$this->buffer      = new JObject;
		}

		return $reset;
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _returnBuffer($reset = null)
	{
		$buffer = $this->buffer;

		// Reset arguments
		$this->_resetArgs($reset);

		return $buffer;
	}

	/**
	 * Check if method exists and is a string
	 *
	 * return       boolean
	 *
	 * @since       1.0.0
	 **/
	protected function _isMethod($name)
	{
		if (!isset($this->_methods[$name]))
		{
			$this->_methods[$name] = (is_string($name) && method_exists($this, $name));
		}

		return $this->_methods[$name];
	}

	/**
	 * @since       1.0.0
	 **/
	protected function _getBufferPivot()
	{
		$arguments = func_get_args();
		$pivot     = $arguments[0];
		unset($arguments[0]);

		$hash = md5($this->method . serialize($arguments));

		if (!isset($this->_pivots[$hash]))
		{
			if ($this->_isMethod($this->method) && is_string($pivot))
			{
				$buffer = call_user_func_array(array($this, $this->method), $arguments);

				$this->_pivots[$hash]           = new JObject();
				$this->_pivots[$hash]->elements = new JObject(KextensionsArray::pivot((array) get_object_vars($buffer), $pivot));
				$this->_pivots[$hash]->_pivot   = $pivot;

				unset($buffer);
			}
			else
			{
				$this->_pivots[$hash] = new JObject;
			}
		}
		elseif ($this->_pivots[$hash] && $this->_pivots[$hash]->_pivot != $pivot)
		{
			$buffer = (array) get_object_vars($this->_pivots[$hash]->elements);

			$this->_pivots[$hash]->elements = new JObject(KextensionsArray::pivot(KextensionsArray::flatten($buffer), $pivot));
			$this->_pivots[$hash]->_pivot   = $pivot;
		}
		else
		{
			// Reset arguments
			$this->_resetArgs();
		}

		return $this->_pivots[$hash]->get('elements', new JObject);
	}

	/**
	 * @since       1.0.0
	 **/
	protected function _getBufferColumn()
	{
		$arguments = func_get_args();
		$hash      = md5($this->method . serialize($arguments));

		if (!isset($this->_columns[$hash]))
		{
			$column = $arguments[0];
			unset($arguments[0]);

			if ($this->_isMethod($this->method) && is_string($column))
			{
				$buffer                = call_user_func_array(array($this, $this->method), $arguments);
				$this->_columns[$hash] = KextensionsArray::getColumn($buffer, $column);

				unset($buffer);
			}
			else
			{
				$this->_columns[$hash] = array();
			}
		}
		else
		{
			// Reset arguments
			$this->_resetArgs();
		}

		return $this->_columns[$hash];
	}

	/**
	 * @since       1.0.0
	 **/
	public function __call($name, $arguments = array())
	{
		if (!array_key_exists($name, $this->_methods))
		{
			$this->_methods[$name] = preg_split('/(?=Pivot$)|(?=Column$)/x', $name);
		}

		$parts = $this->_methods[$name];
		if (isset($parts[1]))
		{
			$this->_beforeCall($parts[1], $parts[0], $name, $arguments);

			$method       = '_getBuffer' . $parts[1];
			$this->method = $parts[0];

			return call_user_func_array(array($this, $method), $arguments);
		}

		// [TODO] uncooments only for test
		throw new InvalidArgumentException('Method not exists ' . get_class($this) . '::' . $name);
	}

	// abstract protected function _beforeCall( $type, $method, $arguments );
	protected function _beforeCall($type, $method, $name, &$arguments)
	{
	}

}