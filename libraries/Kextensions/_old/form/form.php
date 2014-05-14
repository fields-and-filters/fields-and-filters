<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * KextensionsForm.
 *
 * @since       1.0.0
 */
class KextensionsForm
{
	/**
	 * The name of the form instance.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $name;

	/**
	 * The JRegistry data store for form fields during display.
	 *
	 * @var    object
	 * @since  1.0.0
	 */
	protected $data;

	/**
	 * The is data
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $isData = false;

	/**
	 * The path group and data.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $path = '';

	/**
	 * The object fields.
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $fields = array();

	/**
	 * The ordering.
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $ordering = array();

	/**
	 *  Name function/method name for a callback.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $sort = 'ksort';

	/**
	 * The sort flag.
	 *
	 * @var    numeric
	 * @since  1.0.0
	 */
	protected $sortFlag = SORT_NUMERIC;

	/**
	 * The increment. Increment name if exists. If false this will overwrite old value
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $increment = true;

	/**
	 * Method to instantiate the form object.
	 *
	 * @param   string $name    The name of the form.
	 * @param   array  $options An array of form options.
	 *
	 * @since   1.0.0
	 */
	public function __construct($name, $config = array())
	{
		// Set the name for the form.
		$this->name = $name;

		// Initialise the JRegistry data.
		$this->data = new JRegistry;

		if (array_key_exists('path', $config))
		{
			$this->setPath($config['path']);
		}
	}

	/**
	 * Method to get the form name.
	 *
	 * @return  string  The name of the form.
	 *
	 * @since   1.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Getter for the form data
	 *
	 * @return   JRegistry/boolean  Object with the data or if empty return false;
	 *
	 * @since    1.0.0
	 */
	public function getData()
	{
		if ($this->isData && $this->data instanceof JRegistry)
		{
			return $this->data;
		}

		return false;
	}

	/**
	 * Getter for the sort type
	 *
	 * @return   string  Name function/method name for a callback
	 *
	 * @since    1.0.0
	 */
	public function getSort()
	{
		return $this->sort;
	}

	/**
	 * Getter for the sort flag
	 *
	 * @return   string  Sort flag
	 *
	 * @since    1.0.0
	 */
	public function getSortFlag()
	{
		return $this->sortFlag;
	}

	/**
	 * Getter for the ordering
	 *
	 * @return   array  ordering
	 *
	 * @since    1.0.0
	 */
	public function getOrdering()
	{
		return $this->ordering;
	}

	/**
	 * Getter for the increment
	 *
	 * @return   array  increment
	 *
	 * @since    1.0.0
	 */
	public function getIncrement()
	{
		return $this->increment;
	}

	/**
	 * Getter for the form field
	 *
	 * @return    SimpleXMLElement/boolean  Object SimpleXMLElement or if not exists return false;
	 *
	 * @since    1.0.0
	 */
	public function getField($name)
	{
		if (!isset($this->fields[$name]))
		{
			return false;
		}

		return $this->fields[$name];
	}

	/**
	 * Getter for the form fields
	 *
	 * @return    array/boolean    Object with the data or if empty return false;
	 *
	 * @since    1.0.0
	 */
	public function getFormFields()
	{
		if (empty($this->fields))
		{
			return false;
		}

		$this->reorder();

		// Check for a callback sort.
		if (strpos($this->sort, '::') !== false && is_callable(explode('::', $this->sort)))
		{
			call_user_func_array(explode('::', $sort), array(&$this->fields, $this->sortFlag));
		}

		// Sort using a callback function if specified.
		elseif (function_exists($this->sort))
		{
			call_user_func_array($this->sort, array(&$this->fields, $this->sortFlag));
		}

		return $this->fields;
	}

	/**
	 * Method to adjust the ordering of a fields.
	 *
	 * @since    1.0.0
	 */
	protected function reorder()
	{
		if (empty($this->ordering))
		{
			return;
		}

		$fields       = $this->fields;
		$this->fields = array();

		foreach ($this->ordering AS $name => $order)
		{
			if (isset($fields[$name]))
			{
				$this->setField($order, $fields[$name]);
				unset($fields[$name]);
			}
		}

		if (!empty($fields))
		{
			$names = array_keys($this->fields);
			asort($names);
			$name = end($names);

			while ($field = array_shift($fields))
			{
				$this->setField($name, $field);
			}
		}
	}

	/**
	 * Set Path for group and data
	 *
	 * @param    string $path path for group and data
	 *
	 * @since        1.0.0
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}

	/**
	 * Method to set the value of a field.
	 *
	 * @param   string $name  The name of the field for which to set the value.
	 * @param   mixed  $value The value to set for the field.
	 * @param   string $path  The optional dot-separated form group path on which to find the field.
	 *
	 * @since   1.0.0
	 */
	public function setData($name, $value, $path = null)
	{
		$path = !is_null($path) ? $path : $this->path;
		$path = !empty($path) ? $path . '.' . $name : $name;

		$this->data->set($path, $value);

		$this->isData = true;

		return $this;
	}

	/**
	 * Method to set sort.
	 *
	 * @return   string  Name function/method name for a callback
	 *
	 * @since    1.0.0
	 */
	public function setSort($sort)
	{
		$this->sort = $sort;

		return $this;
	}

	/**
	 * Method to set sort flag
	 *
	 * @return   mix $flag Sort flag
	 *
	 * @since    1.0.0
	 */
	public function setSortFlag($flag)
	{
		$this->sortFlag = $flag;

		return $this;
	}

	/**
	 * Method to set ordering.
	 *
	 * @param   array $array Ordering array.
	 *
	 * @since   1.0.0
	 */
	public function setOrdering(array $ordering)
	{
		$this->ordering = $ordering;

		return $this;
	}

	/**
	 * Method to set single order.
	 *
	 * @param   mix $name  Name field.
	 * @param   mic $order Order value.
	 *
	 * @since   1.0.0
	 */
	public function addOrder($name, $order)
	{
		$this->ordering[$name] = $order;

		return $this;
	}

	/**
	 * Method to set a increment.
	 *
	 * @param   boolean $increment Increment name if exists. If false this will overwrite old value
	 *
	 * @since   1.0.0
	 */
	public function setIncrement($increment)
	{
		$this->increment = (boolean) $increment;

		return $this;
	}

	/**
	 * Method to set a field XML element.
	 *
	 * @param   mix              $name    The name of field
	 * @param   SimpleXMLElement $element The XML element object representation of the form field.
	 *
	 * @since   1.0.0
	 */
	public function setField($name, SimpleXMLElement $element, $increment = true)
	{
		if ($increment)
		{
			while (array_key_exists($name, $this->fields))
			{
				$name = is_numeric($name) ? $name + 1 : JString::increment($name, 'dash');
			}
		}

		$this->fields[$name] = $element;

		return $this;
	}
}