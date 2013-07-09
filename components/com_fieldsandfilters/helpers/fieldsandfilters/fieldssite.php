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

class FieldsandfiltersFieldsSiteHelper
{
	protected static $_dispatcher;
	
	protected static function getDispatcher()
	{
		if( is_null( self::$_dispatcher ) )
		{
			if( version_compare( JVERSION, 3.0, '<' ) )
			{
				self::$_dispatcher = JDispatcher::getInstance();
			}
			else
			{
				self::$_dispatcher = JEventDispatcher::getInstance();
			}
		}
		
		return self::$_dispatcher;
	}
	
	public static function getFieldsByItemID( $option, $itemID, $fieldsID = null, $state = null, $getAllextensions = true )
	{
		// Load PluginExtensions Helper
		JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		$pluginExtensionsHelper = FieldsandfiltersPluginExtensionsHelper::getInstance();
		
		if( !is_string( $option ) )
		{
			$option = JFactory::getApplication()->input->get( 'option' );
		}
		
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		// Trigger the onFieldsandfiltersPrepareFormField event.
		$extensionName		= (array) self::getDispatcher()->trigger( 'getFieldsandfiltersExtensionName', array( $option ) );
		reset( $extensionName );
		$extensionName 	= current( $extensionName );
		$extensionsID 		= $pluginExtensionsHelper->getExtensionsByNameColumn( 'extension_type_id', $extensionName );
		
		if( empty( $extensionsID ) )
		{
			return self::_returnEmpty();
		}
		
		// Load elements Helper
		JLoader::import( 'helpers.fieldsandfilters.elements', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		if( !( $element = FieldsandfilterselementsHelper::getInstance()->getElementsByItemIDPivot( 'item_id', $extensionsID, $itemID, $state, 3 )->get( $itemID ) ) )
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
		JLoader::import( 'helpers.fieldsandfilters.fields', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		if( !( $fields = FieldsandfiltersFieldsHelper::getInstance()->getFieldsByID( $extensionsID, $fieldsID, 1, true ) ) )
		{
			return self::_returnEmpty();
		}
		
		return new JObject( array( 'element' => $element, 'fields' => $fields ) );
	}
	
	public static function getFieldsByItemIDWithTemplate( $option, $itemID, $fieldsID = null, $state = null, $getAllextensions = true )
	{
		$object 	= self::getFieldsByItemID( $option, $itemID, $fieldsID, $state, $getAllextensions );
		$templateFields = new JObject;
		
		$fields = new JObject( JArrayHelper::pivot( $object->fields->getProperties(), 'field_type' ) );
		
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// [TODO] zmienić kolejność pierwsze $templateFields
		// [TODO] można teraz szukać np po id
		// [TODO] można dodawać parametry
		// Trigger the onFieldsandfiltersPrepareFormField event.
		self::$_dispatcher->trigger( 'getFieldsandfiltersFieldsHTML', array( $fields, $object->element, $templateFields ) );
		
		return $templateFields;
	}
	
	protected static function _returnEmpty()
	{
		return new JObject( array( 'element' => new JObject(), 'fields' => new JObject() ) );
	}
	
}