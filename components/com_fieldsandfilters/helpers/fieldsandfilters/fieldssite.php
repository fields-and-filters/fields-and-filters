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
	public static function getFieldsByItemID( $option = null, $itemID = null, $fieldsID = null,  $getAllextensions = true )
	{
		$app = JFactory::getApplication();
		
		// Load PluginExtensions Helper
		$pluginExtensionsHelper = FieldsandfiltersFactory::getPluginExtensions();
		
		if( is_null( $option ) )
		{
			$option = $app->input->get( 'option' );
		}
		
		$extensionsID = (array) $pluginExtensionsHelper->getExtensionsIDByOption( $option );
		
		
		if( $getAllextensions )
		{
			$extensionsID = array_merge( $extensionsID, (array) $pluginExtensionsHelper->getExtensionsByNameColumn( 'extension_type_id', 'allextensions' ) );
		}
		
		if( empty( $extensionsID ) )
		{
			return self::_returnEmpty();
		}
		
		// Load elements Helper
		$element = false;
		if( !( $isNullItemID = is_null( $itemID ) ) && !( $element = FieldsandfiltersFactory::getElements()->getElementsByItemIDPivot( 'item_id', $extensionsID, $itemID, 1, 3 )->get( $itemID ) ) )
		{
			return self::_returnEmpty();
		}
		
		if( !( $isNullFieldsID = is_null( $fieldsID ) ) && $element )
		{
			$values = 3;
		}
		else if( $isNullFieldsID && $element )
		{
			$fieldsID = array_merge( array_keys( $element->connections->getProperties( true ) ), array_keys( $element->data->getProperties( true ) ) );
			
			if( empty( $fieldsID ) )
			{
				return self::_returnEmpty();
			}
			
			$values = 1;	
		}
		else if( !$isNullFieldsID && $isNullItemID )
		{
			$values = 2;
		}
		else
		{
			return self::_returnEmpty();
		}
		
		// Load Fields Helper
		if( !( $fields = FieldsandfiltersFactory::getFields()->getFieldsByID( $extensionsID, $fieldsID, 1, $values ) ) )
		{
			return self::_returnEmpty();
		}
		
		return new JObject( array( 'element' => $element, 'fields' => $fields ) );
	}
	
	/**
        * @since       1.1.0
        */
	public static function getFieldsByItemIDWithTemplate( $option = null, $itemID = null, $fieldsID = null, $getAllextensions = true, $params = false, $ordering = 'ordering' )
	{
		$object 	= self::getFieldsByItemID( $option, $itemID, $fieldsID, $getAllextensions );
		$templateFields = new JObject;
		
		$fields = $object->fields->getProperties();
		if( empty( $fields ) )
		{
			return $templateFields;
		}
		
		$fields = new JObject( JArrayHelper::pivot( $fields, 'field_type' ) );
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