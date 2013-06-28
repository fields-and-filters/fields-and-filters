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

// Load the Buffer Helper
JLoader::import( 'fieldsandfilters.buffer.buffer', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

/**
 * FieldsandfiltersCacheHelper.
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
abstract class FieldsandfiltersBufferValuesHelper extends FieldsandfiltersBufferHelper
{
        protected $methodValues = null;
        
        /**
	 * Method to get the Elements that reflect extensions type id and states
	 * 
	 * @param	int/array       	$extensionsTypeID	intiger or array of extensions type id
	 * @param	int/array/null		$states		        intiger or array of states
	 *
	 * @return	object		empty or array object elements
	 * @since       1.1.0
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
                                        $this->vars->valuesName = $getValue;
                                        $this->_getBufferValues(); 
                                }
                        }
                        else
                        {
                                $this->methodValues     = $getValues;
                                $this->vars->valuesName = $getValues;
                                $this->_getBufferValues();   
                        }
                        
                        $this->methodValues = null;
                }
        }
        
        /**
	 * 
	 * @since       1.1.0
	 */
        protected function _getBufferValues()
        {
                if( !( $this->_prepareVarsValues() ) || is_null( $this->methodValues ) )
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
                                JLog::add( $e->getMessage(), JLog::ERROR, 'Fieldsandfilters' );
                        } 	
		}
                
                $_vars = $this->vars->values;
                $_vars->elements = array_diff( $_vars->elements, $_vars->elementsAdd );
                if( !empty( $_vars->elements ) )
                {
                        while( $element = array_shift( $_vars->elements ) )
                        {
                                $buffer = $this->buffer->$element;
                                $this->_addValue( $buffer );
                                
                                if( !$this->config->get('elemntsWithoutValues', true ) )
                                {
                                        $element = $buffer->{$this->vars->elementName};
                                        unset( $this->buffer->{$element} );
                                }
                        }
                }
                
                unset( $_vars );
        }
        
        /**
	 * 
	 * @since       1.1.0
	 */
        protected function _prepareVarsValues()
        {
                if( !isset( $this->vars->valuesName ) )
                {
                        JLog::add( 'Not isset vars valuesName', JLog::ERROR, 'Fieldsandfilters' );
                        return false;  
                }
                
                $this->vars->values                    = new stdClass();
                $this->vars->values->elements          = array();
                $this->vars->values->elementsAdd       = array();
                
                return true;
        }
        
        /**
	 * 
	 * @since       1.1.0
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
	 * @since       1.1.0
	 */
        protected function _searchValuesElement( &$element )
        {
                $elementName    = $this->vars->elementName;
                $_vars          = $this->vars->values;
                // if element don't have filter_connection, add to arrry
                if( !isset( $element->{$this->vars->valuesName} ) )
                {
                        array_push( $_vars->elements, $element->$elementName );
                }
                // if we need only elements with filter_connections isn't empty
                elseif( !$this->config->get( 'elemntsWithoutValues', true ) )
                {
                        $_values = get_object_vars( $element->{$this->vars->valuesName} );
                        
                        if( empty( $_values ) )
                        {
                                unset( $this->buffer->{$element->$elementName} );
                        }
                        
                        unset( $_values );
                }
        }
        
        /**
	 * 
	 * @since       1.1.0
	 */
        protected function _testQueryVarsValues()
        {
                return ( !empty( $this->vars->values->elements ) );
        }
        
        /**
	 * 
	 * @since       1.1.0
	 */
        protected function _getQueryValues()
        {
                $query = $this->_db->getQuery( true );
                
		return $query;
        }
        
        /**
	 * 
	 * @since       1.1.0
	 */
        protected function _setDataValue( &$_value )
        {
                $_vars = $this->vars;
                
                if( isset( $_value->{$_vars->elementName} ) && !in_array( $_value->{$_vars->elementName}, $_vars->values->elementsAdd ) )
                {
                        array_push( $_vars->values->elementsAdd, $_value->{$_vars->elementName} );
                }
                
                $this->_addValue( $_value );
        }
        
        /**
	 * 
	 * @since       1.1.0
	 */
        protected function _addValue( &$value ){}
}