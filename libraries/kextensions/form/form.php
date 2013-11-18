<?php
/**
 * @version     1.0.0
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined( 'JPATH_PLATFORM' ) or die;

/**
 * KextensionsForm.
 * 
 * @since       1.0.0
 */
class KextensionsForm
{
	/**
	 * The name of the form instance.
	 * @var    string
	 * @since  1.0.0
	 */
	protected $name;
	
	/**
	 * The JRegistry data store for form fields during display.
	 * @var    object
	 * @since  1.0.0
	 */
	protected $data;
	
	/**
	 * The is data
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $isData = false;
	
	/**
	 * The path group and data.
	 * @var    string
	 * @since  1.0.0
	 */
	protected $path = '';
	
	/**
	 * The object fields.
	 * @var    array
	 * @since  1.0.0
	 */
	protected $fields = array();
	
	/**
	 * Method to instantiate the form object.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @since   1.0.0
	 */
	public function __construct( $name, $config = array() )
	{
		// Set the name for the form.
		$this->name = $name;
		
		// Initialise the JRegistry data.
		$this->data = new JRegistry;
		
		if( array_key_exists( 'path', $config ) )
		{
			$this->setPath( $config['path'] );
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
		if( $this->isData && $this->data instanceof JRegistry )
		{
			return $this->data;
		}
		
		return false;
	}
	
	/**
	 * Getter for the form fields
	 *
	 * @param	string		$sort		Name function/method name for a callback
	 * @param	init		$sort_flags	You may modify the behavior of the sort using the optional parameter
	 * 
	 * @return   	array/boolean  	Object with the data or if empty return false;
	 *
	 * @since    1.0.0
	 */
	public function getFields( $sort = null, $sort_flags = SORT_NUMERIC )
	{
		$sort 	= !is_null( $sort ) ? $sort : 'ksort';
		$fields	= $this->fields;
		
		if( empty( $fields ) )
		{
			return false;
		}
		
		// Check for a callback sort.
		if( strpos( $sort, '::' ) !== false && is_callable( explode( '::', $sort ) ) )
		{
			call_user_func_array( explode( '::', $sort ), array( &$fields, $sort_flags ) );
		}

		// Sort using a callback function if specified.
		elseif( function_exists( $sort ) )
		{
			call_user_func_array( $sort, array( &$fields, $sort_flags ) );
		}
		
		return $fields;
	}
	
	/**
	 * Set Path for group and data
	 *
	 * @param   	string  $path	path for group and data
	 *
	 * @since    	1.0.0
	 */
	public function setPath( $path )
	{
		$this->path = $path;
	}
	
	/**
	 * Method to set the value of a field.
	 *
	 * @param   string  $name   The name of the field for which to set the value.
	 * @param   mixed   $value  The value to set for the field.
	 * @param   string  $path   The optional dot-separated form group path on which to find the field.
	 * 
	 * @since   1.0.0
	 */
	public function setData( $name, $value, $path = null )
	{
		$path = !is_null( $path ) ? $path : $this->path;
		$path = !empty( $path ) ? $path . '.' . $name : $name;
		
		$this->data->set( $path, $value );
		
		$this->isData = true;
	}
	
	/**
	 * Method to set a field XML element.
	 *
	 * @param   mix			$name	  	The name of field
	 * @param   SimpleXMLElement  	$element  	The XML element object representation of the form field.
	 * @param   boolean  		$increment  	Increment name if exists. If false this will overwrite old value
	 *
	 * @since   1.0.0
	 */
	public function setField( $name, SimpleXMLElement $element, $increment = true )
	{
		if( $increment )
		{
			while( array_key_exists( $name, $this->fields ) )
			{
				$name = is_numeric( $name ) ? $name + 1 : JString::increment( $name, 'dash' );
			};
		}
		
		$this->fields[$name] = $element;
	}
}