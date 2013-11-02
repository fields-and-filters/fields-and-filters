<?php
/**
 * @version     1.1.1
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
	 * Simple syntax - #{field_id,item_id-option}
	 * @since       1.2.0
	 **/
	const SYNTAX_SIMPLE = 1;
	
	/**
	 * Simple syntax + context - #{field_id,item_id-option,context}
	 * @since       1.2.0
	 **/
	const SYNTAX_CONTEXT = 2;
	
	/**
	 * Simple syntax + params - #{field_id,item_id-option,{params}}
	 * @since       1.2.0
	 **/
	const SYNTAX_PARAMS = 3;
	
	/**
	 * Simple syntax + context + params - #{field_id,item_id-option,context,{params}}
	 * @since       1.2.0
	 **/
	const SYNTAX_CONTEXT_PARAMS = 4;
	
	/**
	 * Old syntax - #{field_id,item_id,option}
	 * @since       1.2.0
	 **/
	const SYNTAX_OLD = -1;
	
	/**
	 * @since       1.1.0
	 **/
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
	 * @since       1.2.0
	 **/
	public static function getFieldsLayouts( JObject $object, $params = false, $ordering = 'ordering' )
	{
		$templateFields = new JObject;
		
		if( !isset( $object->fields ) || !isset( $object->element ) )
		{
			return $templateFields;
		}
		
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
	 * @since       1.2.0
	 **/
	public static function getFieldsLayoutsByItemID( $option = null, $itemID = null, $fieldsID = null, $getAllextensions = true, $params = false, $ordering = 'ordering' )
	{
		$object 	= self::getFieldsByItemID( $option, $itemID, $fieldsID, $getAllextensions );
		return self::getFieldsLayouts( $object, $params, $ordering );
	}
	
	/**
	 * @since       1.2.0
	 **/
	public static function preparationContent( &$text, $context = '', $option = null, $itemID = null, $excluded = array(), $syntax = null, $syntaxType = null )
	{
		$syntax 	= $syntax ? $syntax : FieldsandfiltersFactory::getExtensions()->getPluginParams( 'system', 'fieldsandfilters' )->get( 'syntax', '#{%s}' );
		$syntaxType 	= $syntaxType ? $syntaxType : FieldsandfiltersFactory::getExtensions()->getPluginParams( 'system', 'fieldsandfilters' )->get( 'syntax_type', self::SYNTAX_SIMPLE );
		// [TODO] change interpolation to syntax
		
		if( strpos( $syntax, '%s' ) !== false )
		{
			$prefix = explode( '%s', $syntax );
			
			// simple performance check to determine whether bot should process further
			if( !( $prefix = $prefix[0] ) || strpos( $text, $prefix ) === false )
			{
				return true;
			}
			
			/* @deprecated  1.2.0 */
			if( $syntaxType == self::SYNTAX_OLD )
			{
				$regexp = '(?P<field_id>\d+)(?:,(?P<item_id>\d+)|)(?:,(?P<option>[\w.-]+)|)';
			}
			/* @end deprecated  1.2.0 */
			else
			{
				$regexp = '(?P<field_id>\d+)(?:,(?P<item_id>\d+|)(?:-(?P<option>[\w.-]+)|)|)';
			
				switch( $syntaxType )
				{
					case self::SYNTAX_CONTEXT:
						$regexp .= '(?:,(?P<context>[\w.-]+)|)';
					break;
					case self::SYNTAX_PARAMS:
						$regex .= '(?:,(?P<params>{.*?})|)';
					break;
					case self::SYNTAX_CONTEXT_PARAMS:
						$regex .= '(?:,(?P<context>[^{.*?}][\w.-]+)|)(?:,(?P<params>{.*?})|)';
					break;
					case self::SYNTAX_SIMPLE:
					default:
						$syntaxType = self::SYNTAX_SIMPLE;
					break;
				}
			}
			
			$regex = '/' . sprintf( $syntax, $regexp ) . '/i';
			
			// Find all instances of plugin and put in $matches for loadposition
			// $matches[0] is full pattern match
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
			$isParams		= $syntaxType == self::SYNTAX_PARAMS || $syntaxType == self::SYNTAX_CONTEXT_PARAMS;
			
			foreach( $matches as $match )
			{
				/* field id */
				if( !( $fieldID = (int) $match['field_id'] ) )
				{
					$excludes[] = $match[0];
					continue;
				}
				
				/* context */
				if( !empty( $match['context'] ) && $match['context'] != $context )
				{
					$excludes[] = $match[0];
					continue;
				}
				
				/* component - option + item id */
				$_itemID        = ( isset( $match['item_id'] ) && ( $val = (int) $match['item_id'] ) ) ? $val : $itemID;
				$_option         = !empty( $match['option'] ) ? $match['option'] : $option;
				
				if( !property_exists( $extensionsOptions, $_option ) )
				{
					$excludes[] = $match[0];
					continue;
				}
				
				$key = $_option . '-' . $_itemID;
				if( !array_key_exists( $key, $combinations ) )
				{
					$combinations[$key] = array(
						'item_id' 	=> $_itemID,
						'option' 	=> $_option,
						'fields_id'	=> array()
					);
					
					if( $isParams )
					{
						$combinations[$key]['elements'] = array();
					}
					else
					{
						$combinations[$key]['matches'] = array();
					}
				}
				
				/* params */
				if( $isParams )
				{
					$keyElement = $params = null;
					if( !empty( $match['params'] ) )
					{
						$params 	= new JRegistry( $match['params'] );
						$keyElement 	= $params->toString();
						
						if( $keyElement != '{}' )
						{
							$keyElement = md5( $keyElement );
						}
						else
						{
							$keyElement = $params = null;
						}
					}
					
					if( !array_key_exists( $keyElement, $combinations[$key]['elements'] ) )
					{
						$combinations[$key]['elements'][$keyElement] = array(
							'matches'	=> array(),
							'params'	=> $params
						);
					}
					
					$combinations[$key]['elements'][$keyElement]['matches'][$fieldID][] = $match[0];
				}
				else
				{
					$combinations[$key]['matches'][$fieldID] = $match[0];
				}
				
								
				if( !in_array( $fieldID, $combinations[$key]['fields_id'] ) )
				{
					$combinations[$key]['fields_id'][] = $fieldID;
				}
			}
			
			if( !empty( $combinations ) )
			{
				while( $combination = array_shift( $combinations ) )
				{
					$object = self::getFieldsByItemID( $combination['option'], $combination['item_id'], $combination['fields_id'], $getAllextensions );
					
					if( $isParams )
					{
						$isFields	= $object->fields->getProperties(true);
						$isFields 	= !empty( $isFields );
						
						while( $element = array_shift( $combination['elements'] ) )
						{
							if( !$isFields )
							{
								$excludes = array_merge( $excludes, FieldsandfiltersFactory::getArray()->flatten( $element['matches'] ) );
								continue;
							}
							
							$_object 	= clone $object;
							$_fieldsID 	= array_keys( $element['matches'] );
							
							foreach( $_fieldsID AS $_fieldID )
							{
								if( !isset( $_object->fields->$_fieldID ) )
								{
									unset( $_object->fields->$_fieldID );
									
									$excludes = array_merge( $excludes, $element['matches'][$_fieldID] );
									unset( $element['matches'][$_fieldID] );
								}
							}
							
							$fieldsLayouts = self::getFieldsLayouts( $object, false, 'field_id' );
							
							foreach( $combination['matches'] AS $fieldID => &$match )
							{
								$text = str_replace( $match, addcslashes( $fieldsLayouts->get( $fieldID, '' ), '\\$' ), $text );
							}
						}
					}
					else
					{
						$fieldsLayouts = self::getFieldsLayouts( $_object, $element['params'], 'field_id' );
							
						foreach( $element['matches'] AS $fieldID => &$match )
						{
							$text = str_replace( $match, addcslashes( $fieldsLayouts->get( $fieldID, '' ), '\\$' ), $text );
						}
					}
				}
			}
			
			if( !empty( $excludes ) )
			{
				$text = str_replace( array_unique( $excludes ), addcslashes( '', '\\$' ), $text );
			}
		}
		
		return true;
	}
	
	/**
	 * @since       1.0.0
	 **/
	protected static function _returnEmpty()
	{
		return new JObject( array( 'element' => new JObject(), 'fields' => new JObject() ) );
	}
	
	/**
	 * @since       1.1.0
	 * @deprecated  1.2.0
	 * @use		FieldsandfiltersFieldsSite::preparationContent()
	 **/
	public static function preparationConetent( &$text, $option = null, $itemID = null, $syntax = null, $excluded = array() )
	{
		self::preparationContent( $text, '', $option, $itemID, $excluded, $syntax, self::SYNTAX_OLD );
	}
	
	/**
         * @since       	1.1.0
         * @deprecated  	1.2.0
         * @use		FieldsandfiltersFieldsSite::getFieldsLayoutsByItemID()
         **/
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
}