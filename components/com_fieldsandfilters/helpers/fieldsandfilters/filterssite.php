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


class FieldsandfiltersFiltersSiteHelper
{
	protected static $_items = array();
	protected static $_counts = array();
	
	protected static function _checkArg( &$arg )
	{
		$arg = array_unique( (array) $arg );
		JArrayHelper::toInteger( $arg );
	}
	
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
	
	public static function getItemsIDByFilters( $types, $filters = null, $states = null )
	{
		$hash = md5( serialize( func_get_args() ) );
		
		if( !isset( self::$_items[$hash] ) )
		{
			// Get the database object.
			$db 	= JFactory::getDbo();
			$query 	= $db->getQuery(true);
			
			self::_checkArg( $types );
			
			
			try
			{
				if( empty( $types )  )
				{
					throw new RuntimeException( 'Empty types' );
				}
				
				$query->from( $db->quoteName( '#__fieldsandfilters_connections', 'c' ) );
					
				
				if( !is_null( $filters ) )
				{
					$filters = (array) $filters;
					if( !empty( $filters ) )
					{
						if( JArrayHelper::isAssociative( $filters ) )
						{
							if( count( $filters ) > 1 )
							{
								$unions 	= array();
								$subQuery 	= clone $query;
								$subQuery->select( $db->quoteName( 'c.element_id' ) );
								
								foreach( $filters AS $filter => &$filter_values )
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
									$subQuery->where( $db->quoteName( 'c.extension_type_id' ) . ' IN(' . implode( ',', $types ) . ')' );
									$unions[] = $subQuery->__toString();
								}
								
								if( count( $unions ) )
								{
									$subQuery = '(' . implode( PHP_EOL . ') UNION DISTINCT (', $unions ) . PHP_EOL . ')' ;
									$query->clear( 'from' );
									$query->from( '(' . $subQuery . ') AS c' );
									// $query->where( $db->quoteName( 'c.element_id' ) . ' = (' . $subQuery . ')' );
									
								}
								else
								{
									$filters = null;
								}
								
							}
							else
							{
								$filter 	= (int) key( $filters );
								$filter_values 	= current( $filters );
								
								self::_checkArg( $filter_values );
								
								if( $filter && !empty( $filter_values ) )
								{
									$query->where( $db->quoteName( 'c.field_id' ) . ' = ' . $filter );
									$query->where( $db->quoteName( 'c.field_value_id' ) . ' IN(' . implode( ',', $filter_values ) . ')' );
									$query->where( $db->quoteName( 'c.extension_type_id' ) . ' IN(' . implode( ',', $types ) . ')' );
								}
								else
								{
									$filters = null;
								}
							}
						}
						else
						{
							self::_checkArg( $filters );
							if( !empty( $filters ) )
							{
								$query->where( $db->quoteName( 'c.field_id' ) . ' IN(' . implode( ',', $filters ) . ')' );
								$query->where( $db->quoteName( 'c.extension_type_id' ) . ' IN(' . implode( ',', $types ) . ')' );
							}
						}
					}
					
				}
				
				$query->select( 'DISTINCT ' . $db->quoteName( 'e.item_id' ) )
					->join( 'INNER', $db->quoteName( '#__fieldsandfilters_elements', 'e' ) . ' ON ' . $db->quoteName( 'e.element_id' ) . '=' . $db->quoteName( 'c.element_id' ) );
				
				if( empty( $filters ) )
				{
					$states = is_null( $states ) ? array( 1 ) : $states;
					self::_checkArg( $states );
					
					$query->where( $db->quoteName( 'e.state' ) . ' IN (' . implode( ',', $states ) . ')' );
					$query->where( $db->quoteName( 'c.extension_type_id' ) . ' IN(' . implode( ',', $types ) . ')' );
				}
				
				$result = $db->setQuery( $query )->loadColumn();
				
				// echo $query->dump();
				
			}
			catch( RuntimeException $e )
			{
				JLog::add( __METHOD__ . ': ' . $e->getMessage(), JLog::ERROR, 'Fieldsandfilters-FiltersHelper' );
				$result = false;
			}
			
			self::$_items[$hash] = $result;
		}
		
		return self::$_items[$hash];
	}
}