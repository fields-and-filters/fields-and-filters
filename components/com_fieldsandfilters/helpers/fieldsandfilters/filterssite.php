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
class FieldsandfiltersFiltersSiteHelper
{
	/**
	* @since       1.0.0
	*/
	protected static $_items = array();
	
	/**
	* @since       1.0.0
	*/
	protected static $_counts = array();
	
	protected static $filters;
	protected static $types;
	
	
	// [TODO] jeżeli beetween values jest na AND robimy IN() i pozniej w skrypcie sprawdzamy ktore elementy pasuja.
	public static function getFiltersValuesCount( $types, $filters = null, $items = null, $states = null )
	{
		$hash = md5( serialize( func_get_args() ) );
		
		if( !isset( self::$_counts[$hash] ) )
		{
			$db 	= JFactory::getDbo();
			$query 	= $db->getQuery( true );
			
			self::_checkArg( $types );
			
			try
			{
				if( empty( $types )  )
				{
					throw new RuntimeException( 'Empty types' );
				}
				
				$query->select( array(
							$db->quoteName( 'c.field_value_id' ),
							'COUNT(' . $db->quoteName( 'c.field_value_id' ) . ') AS ' . $db->quoteName( 'field_value_count' ),
					) )
					->from( $db->quoteName( '#__fieldsandfilters_connections', 'c' ) )
					->where( $db->quoteName( 'c.extension_type_id' ) . ' IN(' . implode( ',', $types ) . ')' );
				
				if( !is_null( $filters ) )
				{
					self::_checkArg( $filters );
					
					if( !empty( $filters ) )
					{
						$query->where( $db->quoteName( 'c.field_id' ) . ' IN(' . implode( ',', $filters ) . ')' );
					}
				}
				
				$query->join( 'INNER', $db->quoteName( '#__fieldsandfilters_elements', 'e' ) . ' ON ' . $db->quoteName( 'e.element_id' ) . '=' . $db->quoteName( 'c.element_id' ) );
				
				if( !is_null( $items ) )
				{
					self::_checkArg( $items );
					
					if( !empty( $items ) )
					{
						$query->where( $db->quoteName( 'e.item_id' ) . ' IN(' . implode( ',', $items ) . ')' );
					}
				}
				
				if( empty( $items ) )
				{
					$states = is_null( $states ) ? array( 1 ) : $states;
					self::_checkArg( $states );
					
					$query->where( $db->quoteName( 'e.state' ) . ' IN (' . implode( ',', $states ) . ')' );
				}
				
				$query->group( $db->quoteName( 'c.field_value_id' ) );
				
				if( $result = $db->setQuery( $query )->loadObjectList() )
				{
					$counts = array();
					
					while( $count = array_shift( $result ) )
					{
						$counts[$count->field_value_id] = $count->field_value_count;
					}
					
					$result = $counts;
					
					unset( $counts );
				}
				
				// echo nl2br( $query->dump() );
				
			}
			catch( RuntimeException $e )
			{
				JLog::add( __METHOD__ . ': ' . $e->getMessage(), JLog::ERROR, 'Fieldsandfilters-FiltersHelper' );
				$result = false;
			}
			
			self::$_counts[$hash] = $result;
		}
		
		return self::$_counts[$hash];
	}
	
	public static function getItemsIDByFilters( $types, $filters = null, $states = null, $betweenFilters = 'AND', $betweenValues = 'OR' )
	{
		$hash = md5( serialize( func_get_args() ) );
		
		if( !isset( self::$_items[$hash] ) )
		{
			// Get the database object.
			$db 	= JFactory::getDbo();
			$query 	= $db->getQuery(true);
			
			self::_checkArg( $types );
			
			$query->select( 'DISTINCT ' . $db->quoteName( 'e.item_id' ) )
				->from( $db->quoteName( '#__fieldsandfilters_elements', 'e' ) );			
			try
			{
				if( empty( $types )  )
				{
					throw new RuntimeException( 'Empty types' );
				}
				
				self::$types 	= $types;
				
				if( !is_null( $filters ) )
				{
					$filters = (array) $filters;
					if( !empty( $filters ) )
					{
						self::$filters = $filters;
						switch( strtoupper( $betweenFilters . '-' . $betweenValues ) )
						{
							case 'OR-OR':
								self::_prepareQueyrBetweenFieldsOrValuesOr( $query );
							break;
							case 'AND-OR':
								self::_prepareQueyrBetweenFieldsAndValuesOr( $query );
							break;
							default:
								throw new RuntimeException( 'Not exists comparation method' );
							break;
						}
					}
					
				}
				
				if( empty( self::$filters ) )
				{
					$states = is_null( $states ) ? array( 1 ) : $states;
					self::_checkArg( $states );
					
					$query->where( $db->quoteName( 'e.state' ) . ' IN (' . implode( ',', $states ) . ')' );
				}
				
				$query->where( $db->quoteName( 'e.extension_type_id' ) . ' IN(' . implode( ',', self::$types ) . ')' );
				
				$result = $db->setQuery( $query )->loadColumn();
				
				//echo $query->dump();
				//exit;
				
				self::$types = null;
				self::$filters = null;
				
			}
			catch( RuntimeException $e )
			{
				//echo $e->getMessage();
				//exit;
				JLog::add( __METHOD__ . ': ' . $e->getMessage(), JLog::ERROR, 'Fieldsandfilters-FiltersHelper' );
				$result = false;
			}
			
			self::$_items[$hash] = !empty( $result ) ? self::getSimpleItemsID( false, $result ) : self::getSimpleItemsID();
		}
		
		return self::$_items[$hash];
	}
	
	/**
        * @since       1.0.0
        */
	public static function getSimpleItemsID( $empty = true, $itemsID = array() )
	{
		return new JObject( array( 'empty' => $empty, 'itemsID' => (array) $itemsID ) );
	}
	
	protected static function _prepareQueyrBetweenFieldsOrValuesOr( $query )
	{
		$db = JFactory::getDbo();
		
		if( JArrayHelper::isAssociative( self::$filters ) )
		{
			if( count( self::$filters ) > 1 )
			{
				$unions 	= array();
				$subQuery 	= $db->getQuery(true);
				$subQuery->select( $db->quoteName( 'c.element_id' ) )
					->from( $db->quoteName( '#__fieldsandfilters_connections', 'c' ) );
					
				foreach( self::$filters AS $filter => &$filter_values )
				{
					$filter 	= (int) $filter;
					
					self::_checkArg( $filter_values );
					
					if( !$filter || empty( $filter_values ) )
					{
						continue;
					}
					
					$subQuery->clear( 'where' );
					$subQuery->where( $db->quoteName( 'c.field_id' ) . ' = ' . $filter );
					$subQuery->where( $db->quoteName( 'c.field_value_id' ) . ' IN(' . implode( ',', $filter_values ) . ')' );
					$subQuery->where( $db->quoteName( 'c.extension_type_id' ) . ' IN(' . implode( ',', self::$types ) . ')' );
					$unions[] = $subQuery->__toString();
				}
				
				if( count( $unions ) )
				{
					$subQuery = '(' . implode( PHP_EOL . ') UNION DISTINCT (', $unions ) . PHP_EOL . ')' ;
					$query->join( 'INNER', '(' . $subQuery . ') AS `c` ON ' . $db->quoteName( 'c.element_id' ) . ' = ' . $db->quoteName( 'e.element_id' ) );					
				}

				
			}
			else
			{
				self::_prepareQueyrFilterAssociativeArray( $query );
			}
		}
		else
		{
			self::_prepareQueyrFilterArray( $query );
		}
	}
	
	protected static function _prepareQueyrBetweenFieldsAndValuesOr( $query )
	{
		$db = JFactory::getDbo();
		
		if( JArrayHelper::isAssociative( self::$filters ) )
		{
			if( count( self::$filters ) > 1 )
			{
				foreach( self::$filters AS $filter => &$filter_values )
				{
					$filter 	= (int) $filter;
					
					self::_checkArg( $filter_values );
					
					if( !$filter || empty( $filter_values ) )
					{
						continue;
					}
					
					$alias = 'c' . $filter ;
					$and = array(
							$db->quoteName( $alias . '.element_id' ) . ' = ' . $db->quoteName( 'e.element_id' ),
							$db->quoteName( $alias . '.field_id' ) . ' = ' . $filter,
							$db->quoteName( $alias . '.field_value_id' ) . ' IN(' . implode( ',', $filter_values ) . ')'
						);
					
					$query->join( 'INNER', $db->quoteName( '#__fieldsandfilters_connections', $alias ) . ' ON ' . implode( ' AND ', $and ) );
					
					unset( $and );
				}
			}
			else
			{
				self::_prepareQueyrFilterAssociativeArray( $query );
			}
		}
		else
		{
			self::_prepareQueyrFilterArray( $query );
		}
	}
	
	protected static function _prepareQueyrFilterAssociativeArray( $query )
	{
		$db = JFactory::getDbo();
		
		$filter 	= (int) key( self::$filters );
		$filter_values 	= current( self::$filters );
		
		self::_checkArg( $filter_values );
		
		if( $filter && !empty( $filter_values ) )
		{
			$query->join( 'INNER', $db->quoteName( '#__fieldsandfilters_connections', 'c' ) . ' ON ' . $db->quoteName( 'c.element_id' ) . ' = ' . $db->quoteName( 'e.element_id' ) )
				->where( $db->quoteName( 'c.field_id' ) . ' = ' . $filter )
				->where( $db->quoteName( 'c.field_value_id' ) . ' IN(' . implode( ',', $filter_values ) . ')' );
		}
		else
		{
			self::$filters = null;
		}
	}
	
	protected static function _prepareQueyrFilterArray( $query )
	{
		$db = JFactory::getDbo();
		
		self::_checkArg( self::$filters );
		if( !empty( self::$filters ) )
		{
			$query->join( 'INNER', $db->quoteName( '#__fieldsandfilters_connections', 'c' ) . ' ON ' . $db->quoteName( 'c.element_id' ) . ' = ' . $db->quoteName( 'e.element_id' ) )
				->where( $db->quoteName( 'c.field_id' ) . ' IN(' . implode( ',', self::$filters ) . ')' );
		}
	}
	
	protected static function _prepareQueyrBetweenFieldsOrValuesAnd()
	{
	}
	
	protected static function _prepareQueyrBetweenFieldsAndValuesAnd( $filters )
	{
	}
	
	protected static function _checkArg( &$arg )
	{
		$arg = array_unique( (array) $arg );
		JArrayHelper::toInteger( $arg );
	}
}