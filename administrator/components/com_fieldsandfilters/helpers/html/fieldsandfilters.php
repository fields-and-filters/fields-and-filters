<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		fieldsandfilters.administrator
 * @subpackage		com_fieldsandfilters
 */
abstract class JHtmlFieldsandfilters
{
	/**
	 * Append a required item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function dropdownRequired( $checkboxId, $prefix = '' )
	{
		$task = $prefix . 'required';
		JHtml::_( 'dropdown.addCustomItem', JText::_( 'COM_FIELDSANDFILTERS_HTML_REQUIRED_ITEM' ), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"' );
		return;
	}

	/**
	 * Append an unrequired item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function dropdownUnrequired( $checkboxId, $prefix = '' )
	{
		$task = $prefix . 'unrequired';
		JHtml::_( 'dropdown.addCustomItem', JText::_( 'COM_FIELDSANDFILTERS_HTML_UNREQUIRED_ITEM' ), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"' );
		return;
	}
	
	/**
	 * Append an published only admin item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function dropdownOnlyAdmin( $checkboxId, $prefix = '' )
	{
		$task = $prefix . 'onlyadmin';
		JHtml::_( 'dropdown.addCustomItem', JText::_( 'COM_FIELDSANDFILTERS_HTML_ONLYADMIN' ), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"' );
		return;
	}
	
	/**
	 * @param   int $value	The state value
	 * @param   int $i
	 */
	public static function required( $value = 0, $i, $prefix = '', $enabled = true, $checkbox = 'cb' )
	{
		JHtml::_('bootstrap.tooltip');
		
		// Array of image, task, title, action
		$states = array(
				0 => array(
						'star-empty',
						'required',
						'COM_FIELDSANDFILTERS_HTML_UNREQUIRED_ITEM',
						'COM_FIELDSANDFILTERS_HTML_TOGGLE_TO_REQUIRED_ITEM'
					),
				1 => array(
						'star',
						'unrequired',
						'COM_FIELDSANDFILTERS_HTML_REQUIRED_ITEM',
						'COM_FIELDSANDFILTERS_HTML_TOGGLE_TO_UNREQUIRED_ITEM'
					)
		);
		
		$state	= JArrayHelper::getValue( $states, (int) $value, $states[1] );
		
		if( $enabled )
		{
			$html[]	= '<a href="#" class="btn btn-micro hasTooltip' . ( $value == 1 ? ' active' : '' ) . '" title="' . JText::_( $state[3] ) . '"';
			$html[] = ' onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $state[1] . '\')">';
			$html[] = '	<i class="icon-' . $state[0] . '"> </i>';
			$html[] = '</a>';
		}
		else
		{
			$html[]	= '<a class="btn btn-micro hasTooltip disabled' . ( $value == 1 ? ' active' : '' ) . '" title="' . JText::_( $state[2] ) . '">';
			$html[] = '	<i class="icon-' . $state[0] . '"></i>';
			$html[] = '</a>';
		}

		return implode( "\n", $html );
	}
	
	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @param   array  $config  An array of configuration options.
	 *                          This array can contain a list of key/value pairs where values are boolean
	 *                          and keys can be taken from 'published', 'unpublished', 'archived', 'trash', 'all'.
	 *                          These pairs determine which values are displayed.
	 *
	 * @return  string  The HTML code for the select tag
	 *
	 * @since   11.1
	 */
	public static function publishedOptions( $config = array() )
	{
		// Build the active state filter options.
		$options = array();
		if( !array_key_exists( 'published', $config ) || $config['published'] )
		{
			$options[] = JHtml::_('select.option', '1', 'JPUBLISHED');
		}
		if( !array_key_exists( 'unpublished', $config ) || $config['unpublished'] )
		{
			$options[] = JHtml::_( 'select.option', '0', 'JUNPUBLISHED' );
		}
		if( !array_key_exists( 'adminonly', $config ) || $config['adminonly'] )
		{
			$options[] = JHtml::_( 'select.option', '-1', 'COM_FIELDSANDFILTERS_HTML_ONLYADMIN' );
		}
		return $options;
	}
	
	
	 /**
	 * Method to get the available options plugin item type. value & text
	 * 
	 * @return	array	array associate value and text
	 * @since	1.00
	 */
	public static function pluginTypesOptions( $excluded = array() )
	{
		$options 	= array();
		
		// Load PluginTypes Helper
		JLoader::import( 'helpers.fieldsandfilters.plugintypes', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		// Load Extensions Helper
		JLoader::import( 'helpers.fieldsandfilters.extensionshelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		if( $pluginTypes = get_object_vars( FieldsandfiltersPluginTypesHelper::getInstance()->getTypes( true ) ) )
		{
			while( $pluginType = array_shift( $pluginTypes ) )
			{
				if( !in_array( $pluginType->name, $excluded ) )
				{
					// load plugin language
					FieldsandfiltersExtensionsHelper::loadLanguage( 'plg_' . $pluginType->type . '_' . $pluginType->name, JPATH_ADMINISTRATOR );
					
					$options[] = JHtml::_( 'select.option', $pluginType->name, JText::_( $pluginType->title ) );
				}
			}
		}
		
		return $options;
	}
	
	 /**
	 * Method to get the available options plugin item type. value & text
	 * 
	 * @return	array	array associate value and text
	 * @since	1.00
	 */
	public static function pluginExtensionsOptions( $excluded = array() )
	{
		$options 	= array();
		
		// Load pluginExtensions Helper
		JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		// Load Extensions Helper
		JLoader::import( 'helpers.fieldsandfilters.extensionshelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		if( $pluginExtensions = get_object_vars( FieldsandfiltersPluginExtensionsHelper::getInstance()->getExtensions( true ) ) )
		{
			while( $pluginExtension = array_shift( $pluginExtensions ) )
			{
				if( !in_array( $pluginExtension->name, $excluded ) )
				{
					// load plugin language
					FieldsandfiltersExtensionsHelper::loadLanguage( 'plg_' . $pluginExtension->type . '_' . $pluginExtension->name, JPATH_ADMINISTRATOR );
					
					$options[] = JHtml::_( 'select.option', $pluginExtension->extension_type_id, JText::_( $pluginExtension->title ) );
				}
			}
		}
		
		return $options;
	}
	
	/**
	 * Method to get the available options fields item. value & text
	 * 
	 * @return	array	array associate value and text
	 * @since	1.00
	 */
	public static function fieldsOptions( $modes = 'values', $state = 1 )
	{
		$options 	= array();
		
		// Load PluginTypes Helper
		JLoader::import( 'helpers.fieldsandfilters.plugintypes', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		$pluginTypesHelper = FieldsandfiltersPluginTypesHelper::getInstance();
		
		// Load pluginExtensions Helper
		JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		$extenionsID = FieldsandfiltersPluginExtensionsHelper::getInstance()->getExtensionsColumn( 'extension_type_id' );
		
		// Load Fields Helper
		JLoader::import( 'helpers.fieldsandfilters.fields', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		$fieldsHelper = FieldsandfiltersFieldsHelper::getInstance();
		
		if( is_array( $modes ) )
		{
			$modes =  FieldsandfiltersArrayHelper::flatten( $pluginTypesHelper->getModes( $modes ) );
		}
		elseif( is_string( $modes ) )
		{
			$modes = $pluginTypesHelper->getMode( $modes );
		}
		
		if( !empty( $modes ) )
		{
			$fields = $fieldsHelper->getFieldsByModeID( $extenionsID, $modes, $state );
		}
		else
		{
			$fields = $fieldsHelper->getFields( $extenionsID, $state );
		}
		
		if( $fields = get_object_vars( $fields ) )
		{
			while( $field = array_shift( $fields ) )
			{
				$options[] = JHtml::_( 'select.option', $field->field_id, $field->field_name );
			}
		}
		
		return $options;
	}
	
	/**
	 * Method to get the available options field values item. value & text
	 * 
	 * @return	array	array associate value and text
	 * @since	1.00
	 */
	public static function fieldValuesOptions( $fieldID, $state = 1 )
	{
		$options 	= array();
		
		// Load pluginExtensions Helper
		JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		$extenionsID = FieldsandfiltersPluginExtensionsHelper::getInstance()->getExtensionsColumn( 'extension_type_id' );
		
		// Load Fields Helper
		JLoader::import( 'helpers.fieldsandfilters.fields', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );		
		$field = FieldsandfiltersFieldsHelper::getInstance()->getFieldsByID( $extenionsID, $fieldID, $state, true );
		
		if( ( $field = $field->get( $fieldID ) ) && ( $values = $field->values->getProperties( true ) ) )
		{
			while( $value = array_shift( $values ) )
			{
				$options[] = JHtml::_( 'select.option', $value->field_value_id, $value->field_value );
			}
		}
		
		return $options;
	}
	
	/**
	 * Method to generate html code for a list of buttons
	 *
	 * @param   array   $buttons  Array of buttons
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function buttons( $buttons )
	{
		$html = array();
		foreach( $buttons as $button )
		{
			$html[] = JHtml::_( 'fieldsandfilters.button', $button );
		}
		return implode( $html );
	}

	/**
	 * Method to generate html code for a list of buttons
	 *
	 * @param   array|object   $button  Button properties
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function button( $button )
	{
		$user = JFactory::getUser();
		if( !empty( $button['access'] ) )
		{
			if( is_bool( $button['access'] ) )
			{
				if( $button['access'] == false )
				{
					return '';
				}
			}
			else
			{
				// Take each pair of permission, context values.
				for( $i = 0, $n = count( $button['access'] ); $i < $n; $i += 2 )
				{
					if( !$user->authorise( $button['access'][$i], $button['access'][$i+1] ) )
					{
						return '';
					}
				}
			}
		}

		$html[] = '<div class="icon"' . ( empty( $button['id'] ) ? '' : ( ' id="' . $button['id'] . '"' ) ) . '>';
		$html[] = '<a href="' . $button['link'] . '"';
		$html[] = ( empty( $button['target'] ) ? '' : ( ' target="' . $button['target'] . '"' ) );
		$html[] = ( empty( $button['onclick'] ) ? '' : ( ' onclick="' . $button['onclick'] . '"' ) );
		$html[] = ( empty( $button['title'] ) ? '' : ( ' title="' . htmlspecialchars( $button['title'] ) . '"') );
		$html[] = '>';
		$html[] = JHtml::_( 'image', empty( $button['image'] ) ? '' : $button['image'], empty( $button['alt'] ) ? null : htmlspecialchars( $button['alt'] ), null, empty( $button['relative'] ) ? false : (boolean) $button['relative'] );
		$html[] = ( empty( $button['text'] ) ) ? '' : ( '<span>' . $button['text'] . '</span>' );
		$html[] = '</a>';
		$html[] = '</div>';
		return implode( $html );
	}
}
