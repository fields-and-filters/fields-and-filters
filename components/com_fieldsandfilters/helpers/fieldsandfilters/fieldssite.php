<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

// Load the Factory Helper
JLoader::import( 'fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

/**
* @since       1.0.0
*/
class FieldsandfiltersFieldsSiteHelper
{	
	/**
        * @since       1.1.0
        */
	public static function getFieldsByItemID( $option, $itemID, $fieldsID = null, $getAllextensions = true )
	{
		// Load PluginExtensions Helper
		$pluginExtensionsHelper = FieldsandfiltersFactory::getPluginExtensions();
		
		if( !is_string( $option ) )
		{
			$option = JFactory::getApplication()->input->get( 'option' );
		}
		
		$extensionsID = $pluginExtensionsHelper->getExtensionsIDByOption( $option );
		
		if( empty( $extensionsID ) )
		{
			return self::_returnEmpty();
		}
		
		// Load elements Helper
		if( !( $element = FieldsandfiltersFactory::getElements()->getElementsByItemIDPivot( 'item_id', $extensionsID, $itemID, 1, 3 )->get( $itemID ) ) )
		{
			return self::_returnEmpty();
		}
		
		if( $getAllextensions )
		{
			$extensionsID = array_merge( $extensionsID, (array) $pluginExtensionsHelper->getExtensionsByNameColumn( 'extension_type_id', 'allextensions' ) );
		}
		
		if( is_null( $fieldsID ) )
		{
			$fieldsID = array_merge( array_keys( $element->connections->getProperties( true ) ), array_keys( $element->data->getProperties( true ) ) );
		}
		
		if( empty( $fieldsID ) )
		{
			return self::_returnEmpty();
		}
		
		// Load Fields Helper
		if( !( $fields = FieldsandfiltersFactory::getFields()->getFieldsByID( $extensionsID, $fieldsID, 1, 3 ) ) )
		{
			return self::_returnEmpty();
		}
		
		return new JObject( array( 'element' => $element, 'fields' => $fields ) );
	}
	
	/**
        * @since       1.1.0
        */
	public static function getFieldsByItemIDWithTemplate( $option, $itemID, $fieldsID = null, $getAllextensions = true, $params = false, $ordering = 'ordering' )
	{
		$object 	= self::getFieldsByItemID( $option, $itemID, $fieldsID, $getAllextensions );
		$templateFields = new JObject;
		
		$fields = new JObject( JArrayHelper::pivot( $object->fields->getProperties(), 'field_type' ) );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		FieldsandfiltersFactory::getDispatcher()->trigger( 'getFieldsandfiltersFieldsHTML', array( $templateFields, $fields, $object->element, $params, $ordering ) );
		
		return $templateFields;
	}
	
	/**
        * @since       1.0.0
        */
	protected static function _returnEmpty()
	{
		return new JObject( array( 'element' => new JObject(), 'fields' => new JObject() ) );
	}
	
}