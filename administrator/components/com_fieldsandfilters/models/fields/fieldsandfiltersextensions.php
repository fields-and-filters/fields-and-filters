<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldFieldsandfiltersExtensions for a select list of type plugins.
 * @since       1.2.0
 */
class JFormFieldFieldsandfiltersExtensions extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since       1.2.0
	 */
	protected $type = 'FieldsandfiltersExtensions';
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.2.0
	 */
	protected function getOptions()
	{
		$exclude = (string) $this->element['exclude'];
		$excluded = array();
		
		if( !empty( $exclude ) )
		{
			$exclude = explode(',', $exclude);
			$excluded = array_map('trim', $exclude);
		}
		$options = JHtml::_( 'FieldsandfiltersHtml.options.extensions',  $excluded);
		
		// Merge any additional options in the XML definition.
		$options = array_merge( parent::getOptions(), $options );
		
		return $options;
	}
}