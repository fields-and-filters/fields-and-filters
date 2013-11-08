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
 * FieldsandfiltersForm.
 * 
 * @since       1.0.0
 */
class FieldsandfiltersForm extends KextensionsForm
{	
	/**
	 * The JObject elements store for form fields during display.
	 * @var    object
	 * @since  1.0.0
	 */
	protected $elements;
	
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
		parent::__construct( $name, $config );
		
		$this->elements = new JObject;
	}
	
	/**
	 * Getter elements
	 *
	 * @return   JObject	Object with the elements
	 *
	 * @since    1.0.0
	 */
	public function getElements()
	{
		return $this->elemenets;
	}
	
	/**
	 * Method to set a element value.
	 *
	 * @param   string		$name	  	The name of field.
	 * @param   mix  		$value  	The element value.
	 * 
	 * @since   1.0.0
	 */
	public function setElement( $name, $value )
	{
		$this->elements->set( $name, $value );
	}
	
	/**
	 * Method to set elements.
	 *
	 * @param   JObject	$elements	Elements values
	 *
	 * @since   1.0.0
	 */
	public function setElements( JObject $elements )
	{
		$this->elements = $elements;
	}
}