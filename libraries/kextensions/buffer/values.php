<?php
/**
 * @version     1.0.0
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined( 'JPATH_PLATFORM' ) or die;

/**
 * KextensionsBufferValues.
 * 
 * @since       1.0.0
 */
abstract class KextensionsBufferValues extends KextensionsBuffer implements KextensionsBufferInterfaceValues
{
	/*
	 * @since       1.0.0
	 */
        protected $methodValues = null;
	
	/**
	 * Temporarily values elements var
	 * @since       1.0.0
	 */
	protected $_valuesElements = array();
	
	/**
	 * Temporarily values elements add var
	 * @since       1.0.0
	 */
	protected $_valuesElementsAdd = array();
        
        /**
	 * Method to get the Elements that reflect extensions type id and states
	 * 
	 * @param	int/array       	$extensionsTypeID	intiger or array of extensions type id
	 * @param	int/array/null		$states		        intiger or array of states
	 *
	 * @return	object		empty or array object elements
	 * @since       1.0.0
	 */
	protected function _prepareBuffer()
        {
		parent::_prepareBuffer();
                
                $getValues = $this->config->get( 'getValues' );
                
                if( !empty( $getValues ) )
                {
                        if( is_array( $getValues ) )
                        {
                                while( $getValue = array_shift( $getValues ) )
                                {
                                        $this->methodValues     = $getValue;
                                        $this->_getBufferValues(); 
                                }
                        }
                        else
                        {
                                $this->methodValues     = $getValues;
                                $this->_getBufferValues();   
                        }
                        
                        $this->methodValues = null;
                }
        }
        
        /**
	 * 
	 * @since       1.0.0
	 */
        protected function _getBufferValues()
        {
                if( !( $this->Values() ) || is_null( $this->methodValues ) )
                {
                        return;
                }
                
                $this->_searchValuesElements();
                
                // If array extensions type ids isn't empty, we get elements from database
		if( $this->_testQueryVarsValues() )
		{
                        try
                        {
                                $query = $this->_getQueryValues();
                                
                                // Load result of elemensts
                                if( $_values = $this->_db->setQuery( $query )->loadObjectList() )
                                {
                                        // Add elements to extension type cache
                                        while( $_value = array_shift( $_values ) )
                                        {
                                                $this->_setDataValue( $_value );
                                        }
                                }
                        }
                        catch( RuntimeException $e )
                        {
                                JLog::add( $e->getMessage(), JLog::ERROR, 'Kextensions' );
                        } 	
		}
                
                $this->_valuesElements = array_diff( $this->_valuesElements, $this->_valuesElementsAdd );
                if( !empty( $this->_valuesElements ) )
                {
                        while( $element = array_shift( $this->_valuesElements ) )
                        {
                                $buffer = $this->buffer->$element;
                                $this->_addValue( $buffer );
                                
                                if( !$this->config->get('elemntsWithoutValues', true ) )
                                {
                                        $element = $buffer->{$this->getForeignName()};
                                        unset( $this->buffer->{$element} );
                                }
                        }
                }
        }
        
        /**
	 * 
	 * @since       1.0.0
	 */
        protected function _searchValuesElements()
        {
                $elements = get_object_vars( $this->buffer );
                
		while( $element = array_shift( $elements ) )
		{
			$this->_searchValuesElement( $element );
		}
		
		unset( $elements, $element );
        }
        
        /**
	 * 
	 * @since       1.0.0
	 */
        protected function _searchValuesElement( &$element )
        {
                $elementName    = $this->getForeignName();
                // if element don't have filter_connection, add to arrry
                if( !isset( $element->{$this->getValuesName()} ) )
                {
                        array_push( $this->_valuesElements, $element->$elementName );
                }
                // if we need only elements with filter_connections isn't empty
                elseif( !$this->config->get( 'elemntsWithoutValues', true ) )
                {
                        $_values = get_object_vars( $element->{$this->getValuesName()} );
                        
                        if( empty( $_values ) )
                        {
                                unset( $this->buffer->{$element->$elementName} );
                        }
                        
                        unset( $_values );
                }
        }
        
        /**
	 * 
	 * @since       1.0.0
	 */
        protected function _testQueryVarsValues()
        {
                return ( !empty( $this->_valuesElements ) );
        }
        
        /**
	 * 
	 * @since       1.0.0
	 */
        protected function _getQueryValues()
        {
                $query = $this->_db->getQuery( true );
                
		return $query;
        }
        
        /**
	 * 
	 * @since       1.0.0
	 */
        protected function _setDataValue( &$_value )
        {
                if( isset( $_value->{$this->getForeignName()} ) && !in_array( $_value->{$this->getForeignName()}, $this->_valuesElementsAdd ) )
                {
                        array_push( $this->_valuesElementsAdd, $_value->{$this->getForeignName()} );
                }
                
                $this->_addValue( $_value );
        }
        
        /**
	 * 
	 * @since       1.0.0
	 */
        protected function _addValue( &$value ){}
	
	/**
	 * Reset arguments
	 * 
	 * @param	boolean 	$reset		reset arguments if you need
	 *
	 * @return      boolean         if is reset
	 * @since       1.0.0
	 **/
	protected function _resetArgs( $reset = null )
	{
		$reset = parent::_resetArgs( $reset );
		
		if( $reset && $this->methodValues )
		{
			$this->_valuesElements 		= array();
			$this->_valuesElementsAdd 	= array();
		}
                
                return $reset;
	}
}