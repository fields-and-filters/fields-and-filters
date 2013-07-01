<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass( 'list' );

// Load the Factory Helper
JLoader::import( 'fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldPluginsType for a select list of type plugins.
 * @since       1.1.0
 */
class JFormFieldFieldsandfiltersPluginTypes extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since       1.0.0
	 */
	protected $type = 'FieldsandfiltersPluginTypes';
	

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since       1.1.0
	 */
	protected function getInput()
	{
		// Initialise variables
		$value		= '';
		$html 		= array();
		$size		= ( $v = $this->element['size'] ) ? ' size="' . $v . '"' : '';
		$class		= ( $v = $this->element['class'] ) ? ' class="' . $v . '"' : 'class="inputbox"';
		$recordId	= (int) $this->form->getValue( 'field_id', 0 );
		$mode		= (int) $this->form->getValue( 'mode', 0 );
		
		$pluginTypesHelper = FieldsandfiltersFactory::getPluginTypes();
		
		if( $mode && ( $pluginType = $pluginTypesHelper->getTypes( true )->get( $this->value ) ) )
		{
			// Load Extensions Helper
			FieldsandfiltersFactory::getExtensions()->loadLanguage( 'plg_' . $pluginType->type . '_' . $pluginType->name, JPATH_ADMINISTRATOR );
			
			$typeName 	= $pluginTypesHelper->getModeName( $mode, 'type' );
			$typeForm	= $pluginType->forms->get( $typeName, new JObject );
			
			if( isset( $typeForm->group->title ) )
			{
				$value = JText::_( $typeForm->title ) . ' [' . JText::_( $typeForm->group->title ) . ']';
			}
			else if( isset( $typeForm->title ) )
			{
				$value = JText::_( $typeForm->title );
			}
		}			
		// Load the javascript and css
		JHtml::_( 'behavior.framework' );
		JHtml::_( 'behavior.modal' );
		
		$query = array(
			       'option' 	=> 'com_fieldsandfilters',
			       'view'		=> 'plugins',
			       'tmpl'		=> 'component',
			       'layout'		=> 'types',
			       'recordId'	=> $recordId
			);
		
		$link = JRoute::_( 'index.php?' . JURI::buildQuery( $query ) );
		
		if( version_compare( JVERSION, 3.0, '<' ) )
		{
			$html[] = '<input type="text" readonly="readonly" disabled="disabled" value="' . $value . '"' . $size . $class . ' />';
			$html[] = '<input type="button" value="' . JText::_( 'JSELECT' ) . '" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\'' . $link . '\'})" />';
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars( $this->value, ENT_COMPAT, 'UTF-8' ) . '" />';
		}
		else
		{
			$html[] = '<span class="input-append">';
			$html[] = '	<input type="text" readonly="readonly" disabled="disabled" value="' . $value . '"' . $size . $class . ' />';
			$html[] = '	<a class="btn btn-primary" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\'' . $link . '\'})">';
			$html[] = '		<i class="icon-list icon-white"></i>';
			$html[] =		JText::_( 'JSELECT' );
			$html[] = '	</a>';
			$html[] = '</span>';
			$html[] = '<input class="input-small" type="hidden" name="' . $this->name . '" value="' . htmlspecialchars( $this->value, ENT_COMPAT, 'UTF-8' ) . '" />';
		}
		
		return implode( "\n", $html );	
		
	}
}