<?php
/**
 * @version     1.1.0
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
	public static function getFieldsByItemID( $option = null, $itemID = null, $fieldsID = null, $getAllextensions = true )
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
	
	public static function preparationConetent( &$text, $option = null, $itemID = null, $interpolation = null, $excluded = array() )
	{
		$interpolation = $interpolation ? $interpolation : FieldsandfiltersFactory::getExtensions()->getPluginParams( 'system', 'fieldsandfilters' )->get( 'interpolation', '#{%s}' );
		
		if( strpos( $interpolation, '%s' ) !== false )
		{
			$regex = '/' . sprintf( $interpolation, '(.*?)' ) . '/i';
			$prefix = explode( '%s', $interpolation );
			
			// simple performance check to determine whether bot should process further
			if( !( $prefix = $prefix[0] ) || strpos( $text, $prefix ) === false )
			{
				return true;
			}
			
			// Find all instances of plugin and put in $matches for loadposition
			// $matches[0] is full pattern match, $matches[1] is the position
			preg_match_all( $regex, $text, $matches, PREG_SET_ORDER );
			
			if( !$matches )
			{
				return true;
			}
			
			$jinput			= JFactory::getApplication()->input;
			$itemID			= ( $itemID = (int) $itemID ) ? $itemID : $jinput->get( 'id', 0, 'int' );
			$option			= $option ? $option : $jinput->get( 'option' );
			$extensionsOptions 	= FieldsandfiltersFactory::getPluginExtensions()->getExtenionsOptions();
			$combinations 		= array();
			$getAllextensions 	= true;
			$isExcluded		= !empty( $excluded ) && is_array( $excluded );
			$excludes		= array();
			
			foreach( $matches as $match )
			{
				$matcheslist 	= explode( ',', $match[1] );
				
				$fieldID 	= (int) $matcheslist[0];
				if( $isExcluded && in_array( $fieldID, $excluded ) )
				{
					if( !in_array( $match[0], $excludes ) )
					{
						$excludes[] = $match[0];
					}
					continue;
				}
				
				$_option = ( array_key_exists( 2, $matcheslist ) && ( $_option = trim( $matcheslist[2] ) ) ) ? $_option : $option;
				if( !property_exists( $extensionsOptions, $_option ) )
				{
					if( !in_array( $match[0], $excludes ) )
					{
						$excludes[] = $match[0];
					}
					continue;
				}
				
				$_itemID 	= ( array_key_exists( 1, $matcheslist ) && ( $_itemID = (int) $matcheslist[1] ) ) ? $_itemID : $itemID;
				$key 		= $_option . '-' . $_itemID;
				
				if( !array_key_exists( $key, $combinations ) )
				{
					$combinations[$key] = array(
									'item_id' 	=> $_itemID,
									'option' 	=> $_option,
									'matches'	=> array()
							);
				}
				
				$combinations[$key]['fields_id'][] = $fieldID;
				$combinations[$key]['matches'][$fieldID] = $match[0];
			}
			
			if( !empty( $combinations ) )
			{
				while( $combination = array_shift( $combinations ) )
				{
					$fields = self::getFieldsByItemIDWithTemplate( $combination['option'], $combination['item_id'], $combination['fields_id'], $getAllextensions, false, 'field_id' );
					
					foreach( $combination['matches'] AS $fieldID => &$match )
					{
						$text = str_replace( $match, addcslashes( $fields->get( $fieldID, '' ), '\\$' ), $text );
					}
				}
			}
			
			if( !empty( $excludes ) )
			{
				$text = str_replace( $excludes, addcslashes( '', '\\$' ), $text );
			}
		}
	}
	
	/**
        * @since       1.0.0
        */
	protected static function _returnEmpty()
	{
		return new JObject( array( 'element' => new JObject(), 'fields' => new JObject() ) );
	}
	
}