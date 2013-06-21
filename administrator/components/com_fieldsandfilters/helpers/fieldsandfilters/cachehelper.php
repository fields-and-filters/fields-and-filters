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

// Load the Array Helper
JLoader::import( 'helpers.fieldsandfilters.arrayhelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );

/**
 * FieldsandfiltersCacheHelper.
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
abstract class FieldsandfiltersCacheHelper
{
        /**
         * The elements instance.
         * 
	 * @var    FieldsandfiltersElementsHelper 
	 * @since  1.00
	 */
	protected static $_instance = array();
        
        /**
	 * Database Connector
	 *
	 * @var    object
	 * @since  1.00
	 */
        protected $_db;
        
        /**
	 * Temp varibles for method and query
	 *
	 * @var    object
	 * @since  1.00
	 */
        protected $_vars;
	
	/**
         * All cache object elements and values
         * 
	 * @var    object.
	 * @since  1.00
	 */
	protected $_data;
        
        /**
         * Temp varibles elements and values when method in running
         * 
	 * @var    object.
	 * @since  1.00
	 */
        protected $_cache;
        
        /**
         * Cache column elements and values
         * 
	 * @var    object.
	 * @since  1.00
	 */
        protected $_columns = array();
        
        /**
         * Cache pivot elements and values
         * 
	 * @var    object.
	 * @since  1.00
	 */
        protected $_pivots = array();
        
        /**
         * Cache name methods
         * 
	 * @var    object.
	 * @since  1.00
	 */
        protected $_methods;
	
	/**
         * An array of names that don't exists
         * 
	 * @var    array 
	 * @since  1.00
	 */
	protected $_not = array( 'items' => '__notItems', 'elements' => '__notElements' );
	
	/**
         * The array types
         * 
	 * @var    array  
	 * @since  1.00
	 */
	protected $_types = array();
        
        /**
         * The array/null of elements or items id 
	 * @var    null/array 
	 * @since  1.00
	 */
	protected $_elements = array();
        
        protected $_notElements = array();
        
        /**
         * The array/null of states
         * 
	 * @var    null/array  
	 * @since  1.00
	 */
	protected $_states = array();
        
        /**
         * Info about basic configuration
         * 
	 * @var    array 
	 * @since  1.00
	 */
        protected $_info = array(
                        'stringTypes'                => false,
                        'numericTypes'               => false,
                        'stringElements'             => false,
                        'numericElements'            => false,
                        'elemntsWithoutValues'       => true,
                        'stateName'                  => 'state'
                );
        
        /**
         * The array configuration
         * 
	 * @var    array  
	 * @since  1.00
	 */
        protected $_config = array();
	
	/**
         * The information on whether the reset variables
         * 
	 * @var    boolean 
	 * @since  1.00
	 */
	protected $_reset = true;
        
        protected $_debug = false;
        
        // instace of logger
        protected $_log = null;
        
        /**
	 * Constructor
	 * 
	 * @since   1.00
	 */
	public function __construct( $debug = null )
	{
		// Instantiate the internal object.
                $this->_db      = JFactory::getDbo();
                $this->_data    = new stdClass;
                $this->_cache   = new JObject;
                $this->_vars    = new stdClass;
                $this->_methods = new stdClass;
                
                
                // <TODO> zamiast JFactory::getConfig()->get( 'debug', false );
                // zmieniamy na JComponentHelper::getParams('fieldsandfilters')->get( 'enable_logging', false );
                $this->_debug   = !is_null( $debug ) ? (boolean) $debug : JFactory::getConfig()->get( 'debug', false );
                
                if( $this->_debug )
		{
                        if( is_null( $this->_log ) )
                        {
                                $options = array(
                                                'format' => '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}',
                                                'text_file' => 'fieldsandfilters.php'
                                        );
                                
                                $this->_log = JLog::addLogger( $options) ;     
                        }
                        
                        JLog::add( 'Start class: ' . get_class( $this ) );
		}
	}
        
        public function __destruct()
        {
                if( $this->_debug )
		{
                        $this->_log = null;
                        JLog::add( 'End class: ' . get_class( $this ) . PHP_EOL );       
                }
        }
        
        /**
	 * Returns a reference to the global FieldsandfiltersElementsHelper object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $fafe = FieldsandfiltersElementsHelper::getInstance();
	 *
	 * @return  FieldsandfiltersElementsHelper
	 *
	 * @since   11.1
	 */
	public static function getInstance( $debug = false )
	{
                $name = get_called_class();
                
		// Only create the object if it doesn't exist.
		if( empty( self::$_instance[$name] ) )
		{
			self::$_instance[$name] = new $name( $debug );
		}

		return self::$_instance[$name];
	}
        
        /**
	 * Method to get date of cache
	 * @param	int	        $extension_type_id	extension type id
	 * @param	array		$config		        array of configuration
	 *
	 * @return	instance of date type
	 * 
	 * @since	2.5
	 */
        protected function &_getData( $name )
        {
		/**
		 * Create new cache array filters
		 * objects	elements	object of elements
		 * array	__state		witch state you get when you get full elements from type
		 */
		
		if( !isset( $this->_data->$name ) )
		{
                        $data = $this->_data->$name = new JObject( array( 'elements' => new JObject, '__states' => array() ) );
                        $data->setProperties( array_fill_keys( array_values( $this->_not ), array() ) );
		}
                
		return $this->_data->$name;
        }
        
        /**
	 * Check arguments
	 *
	 * @param	int/array/string       	$types	                types ( e.g., extension_type_id )
	 * @param	int/array/string       	$elements		elements ( e.g., element_id, item_id, field_id  )
	 * @param	int/array/null		$states		        states
	 *
	 * @return  boolean
	 * 
	 * @since	1.00
	 **/
	protected function _checkArgs( $types, $elements = null, $states = null )
	{
                $this->_config += $this->_info;
                
                // Preparation type
                $this->_checkArg( $types, 'Types' );
		
		if( empty( $types ) )
		{
			return false;
		}
		
		$this->_types = $types;
                reset( $this->_types );
		
		if( !is_null( $elements ) )
		{
                        // Preparation elements
                        $this->_checkArg( $elements, 'Elements' );
			
			if( empty( $elements ) )
			{
				return false;
			}
			
			$this->_elements = $elements;
                        reset( $this->_elements );
		}
		
		// Preparation states. Only unique and intiger ids
		$states = is_null( $states ) ? array( 1 ) : array_unique( (array) $states );
		JArrayHelper::toInteger( $states );
		
		$this->_states = $states;
                
		return true;
	}
        
        /**
	 * Check siingle argument
	 *
	 * @param	array       	&$arg	        check if elements in array are the string or numeric
	 * @param	stirng       	$name		name of type or element
	 * 
	 * @since	1.00
	 **/
        protected function _checkArg( &$arg, $name )
        {
                $arg = array_unique( (array) $arg );
                
                // Check array type elements and add this information to configuration
                if( isset( $this->_config['string' . $name] ) && $this->_config['string' . $name] )
                {                        
                        $this->_config['string' . $name] = true;
                }
                else
                {
                        JArrayHelper::toInteger( $arg );
                        
                        $this->_config['numeric' . $name] = true;
                }               
                
                reset( $arg );
        }
        
        /**
	 * Reset arguments
	 * 
	 * @param	boolean 	$reset		reset arguments if you need
	 *
	 * @since	1.00
	 **/
	protected function _resetArgs( $reset = null )
	{
		$reset = !is_null( $reset ) ? (boolean) $reset : $this->_reset;
		
		if( $reset )
		{
			$this->_types    	= array();
			$this->_elements 	= array();
                        $this->_notElements 	= array();
			$this->_states 		= array();
			$this->_reset		= true;
                        $this->_vars            = new stdClass;
                        $this->_methods         = new stdClass;
                        $this->_config          = array();
                        $this->_cache           = new JObject;
		}
	}
        
        /**
	 * Add new elements to don't exists elements
	 * 
	 * @param	array  		$elements		elements ( e.g., element_id, item_id, field_id  )
	 * @param	string  	&$notType	        key name in array $this->_not
	 *
	 * @since	1.00
	 **/
	protected function _setNot( $elements, $notName = 'elements' )
	{
		if( !empty( $elements ) )
		{
			$notType = isset( $this->_not[$notName] ) ? $this->_not[$notName] : $this->_not[key($this->_not)];
			
                        reset( $this->_types );
			while( $type = current( $this->_types ) )
			{
                                // Get type date
				$data = $this->_getData( $type );
				
                                // Get not Elements and set new elements
				$_not = $data->get( $notType, array() );
				FieldsandfiltersArrayHelper::setArrays( $_not, $this->_states, $elements );
				$data->set( $notType, $_not );
                                
                                next( $this->_types );
			}
			
			unset( $data, $elements, $_not );
		}
	}
        
        /**
	 * Remoweve all elements of states from don't exists elements
	 * 
	 * @param	int/string  		type		types ( e.g., extension_type_id )
	 * @param	array  	                $states	        states
	 *
	 * @since	1.00
	 **/
	protected function _unsetNot( $type, $states )
	{
                // Get type date
                $data = $this->_getData( $type );
                
                reset( $this->_not );       
                while( $not = current( $this->_not ) )
                {
                        // Get not Elements cache from date
                        $notCache = $data->get( $not, array() );
                        
                        if( !empty( $dataNot ) )
                        {
                                // unset elements from form cahce date
                                $data->set( $not, FieldsandfiltersArrayHelper::unsetKyes( $notCache, $states ) );
                        }
                        
                        next( $this->_not );
                }
	}
        
        /**
	 * Check method name and if exists and add name to cache
	 * 
	 * @param	string          $methodName	        method name
	 * @param	stirng 	        $methodDefault          default method name
	 * @param       stirng          $methodReset            reset method name 
	 *
	 * @return      string          method name/method default
	 * @since	1.00
	 **/
        protected function _checkMethod( $methodName, $methodDefault = null, $methodReset = false )
        {
                if( $methodReset || !( isset( $this->_methods->$methodName ) && is_string( $this->_methods->$methodName ) ) )
                {
                        $this->_methods->$methodName = ( isset( $this->_config[$methodName] ) && method_exists( $this, $this->_config[$methodName] ) ? $this->_config[$methodName] : $methodDefault );
                }
                
                if( $this->_debug )
                {
                        JLog::add( 'Trigger method: ' . $this->_methods->$methodName );
                }
                
                return $this->_methods->$methodName;
        }
        
        
/* Methods for _GetCache */
        
        protected function __preparationVars()
        {
                 // We take elements from cache, when they aren't in the cache, we add they to query variables
		$this->_vars->states		= array();
		$this->_vars->types 		= array();
		$this->_vars->notElements       = array();
                
                if( !isset( $this->_config['elementName'] ) || !isset( $this->_config['typeName'] ) )
                {
                        return false;
                }
                
                $this->_vars->typeName          = (string) $this->_config['typeName'];
                $this->_vars->elementName       = (string) $this->_config['elementName'];
                $this->_vars->stateName         = (string) $this->_config['stateName'];
               
                return true;
        }
        
        protected function __searchData()
        {
                $elementsBeforeQuery = $this->_checkMethod( 'beforeQuery', '__beforeQuery' );
                
                while( $type = current( $this->_types ) )
		{
			$this->$elementsBeforeQuery( $type );
                        
                        next( $this->_types );
		}
        }
        
        
        protected function __beforeQuery( $type )
        {
                // Get extension type id from cache
                $data  = $this->_getData( $type );
                
                // The difference states between argument states and cache states
                $dataStates	= $data->get( '__states', array() );
                $_states        = array_diff( $this->_states, $dataStates );
                
                if( !empty( $_states ) )
                {                        
                        // Add difference states to query varible
                        $this->_vars->states += $_states;
                        
                        // When the get states of the need, then add states to the cache extenion type, because we don't need them next time
                        $data->set( '__states', array_merge( $dataStates, $_states ) );
                        
                        // Get elements id from cache, because we don't need get that id's second time from database 
                        $this->_vars->notElements = array_merge( $this->_vars->notElements, array_keys( get_object_vars( $data->get( 'elements', new stdClass ) ) ) );
                        
                        // Add extension type id to query varible
                        array_push( $this->_vars->types, $type );
                        
                        $this->_unsetNot( $type, $_states );
                        
                }
        }
        
        protected function __beforeQueryElements( $type )
        {
                if( ( $emptyEl = empty( $this->_elements ) ) && ( $emptyNotEl = empty( $this->_notElements ) ) )
                {
                        return;
                }
                elseif( $emptyEl )
                {
                        $this->_elements = $this->_notElements;
                }
                elseif( !( isset( $emptyNotEl ) ? $emptyNotEl : empty( $this->_notElements ) ) )
                {
                        $this->_elements = array_unique( array_merge( $this->_elements, $this->_notElements ) );
                }
                
                // Get extension type id from cache
                $data  = $this->_getData( $type );
		
                // The difference states between argument states and cache states
                $_states = array_diff( $this->_states, $data->get( '__states', array() ) );
		
		// Get elements id form cache that don't exist in database
                if( isset( $this->_config['notName'] ) && isset( $this->_not[$this->_config['notName']] ) )
                {
                        $notName = $this->_config['notName'];
                }
                else
                {
                      reset( $this->_not );
                      $notName = key( $this->_not );
                }
                
		$_notElements 	= $data->get( $this->_not[$notName], array() );
		$_notElements 	= FieldsandfiltersArrayHelper::fromArray( $_notElements, $_states );
		
                // before search elements
                $this->{$this->_checkMethod( 'beforeSearchElements', '__beforeSearchElements' )}( $data );
                
		$_elementsID    = array();
                $searchElements = $this->_checkMethod( 'searchElements', '__searchElements' );
                
                reset( $this->_elements );
		while( $elementID = current( $this->_elements ) )
		{
			$this->$searchElements( $data, $elementID, $_elementsID, $_notElements );
			next( $this->_elements );
		}
		
		// We need only isn't exists elements id
		$this->_elements = array_values( array_diff( $this->_elements, $_elementsID ) );
		
		if( !empty( $_states ) && !empty( $this->_elements ) )
		{
			// Add difference states and extension type id to query varibles.
			$this->_vars->states += $_states;
			array_push( $this->_vars->types, $type );
		}
		
		unset( $_notElements, $_elementsID );
        }
        
        protected function __beforeSearchElements( $data ){}
        
        protected function __searchElements( &$data, $elementID, &$_elementsID, &$_notElements )
        {
                // We take element from cache and this id add to array
                if( $_element = $data->elements->get( $elementID ) )
                {
                        if( in_array( $_element->{$this->_vars->stateName}, $this->_states ) )
                        {
                                $this->_cache->set( $_element->{$this->_vars->elementName}, $_element );	
                        }
                        
                        array_push( $_elementsID, $elementID );
                }
                // If argument element id in array ids not exist, add that id to array exist id, because we know that id isn't exist
                elseif( in_array( $elementID, $_notElements ) )
                {
                        if( !in_array( $elementID, $this->_notElements ) )
                        {
                                array_push( $this->_notElements, $elementID );
                        }
                        
                        array_push( $_elementsID, $elementID );
                }
        }
        
        protected function __testQueryVars()
        {
                return ( !empty( $this->_vars->types ) );
        }
        
        protected function __getQuery()
        {
		$query  = $this->_db->getQuery( true );
                
		return $query;
        }
        
        protected function __setData( &$_element )
        {
                $this->_getData( $_element->{$this->_vars->typeName} )->elements->set( $_element->{$this->_vars->elementName}, $_element );
        }
        
        protected function __afterQuery()
        {
                // Get elements from cahce
		while( !empty( $this->_types ) )
		{
			$_elements = get_object_vars( $this->_getData( array_shift( $this->_types ) )->get( 'elements', new JObject ) ) ;
                        
			// Add only those elements are suitable states
			while( $_element = current( $_elements ) )
			{
				if( in_array( $_element->{$this->_vars->stateName}, $this->_states ) )
				{
					$this->_cache->set( $_element->{$this->_vars->elementName}, $_element );
				}
				
				next( $_elements );
			}
		}
        }
        
        protected function _returnCache( $reset = null )
        {
                $elements = $this->_cache;
                
                // Reset arguments 
		$this->_resetArgs( $reset );
                
                return $elements;
        }
        
/* @end Methods for _GetCache */
        
        /**
	 * Method to get the Elements that reflect extensions type id and states
	 * 
	 * @param	int/array       	$extensionsTypeID	intiger or array of extensions type id
	 * @param	int/array/null		$states		        intiger or array of states
	 *
	 * @return	object		empty or array object elements
	 * @since	1.00
	 */
	protected function _getCache( $types, $elements = null, $states = null )
        {
		// We need to get more than one extensions type, we need cache variables for that
                
		// Check arguments
		if( !call_user_func_array( array( $this, '_checkArgs' ), func_get_args() ) )
		{
			return $this->_returnCache();
		}
                
                if( !( $this->{$this->_checkMethod( 'preparationVars', '__preparationVars' )}() ) )
                {
                        return $this->_returnCache();
                }
                
                $this->{$this->_checkMethod( 'searchData', '__searchData' )}();
                
		// If array extensions type ids isn't empty, we get elements from database
		if( $this->{$this->_checkMethod( 'testQueryVars', '__testQueryVars' )}() )
		{
                        $query = $this->{$this->_checkMethod( 'getQuery', '__getQuery' )}();
                        
                        // echo nl2br( $query->dump() );
                        
                        if( $this->_debug )
                        {
                                JLog::add( 'Query: ' . (string) $query );
                        }
                        
                        try
                        {
                                if( $_elements = $this->_db->setQuery( $query )->loadObjectList() )
                                {
                                        $setData = $this->_checkMethod( 'setData', '__setData' );
                                        
                                        // Add elements to extension type cache
                                        while( $_element = array_shift( $_elements ) )
                                        {
                                                $this->$setData( $_element );
                                        }
                                }
                        }
                        catch( RuntimeException $e )
                        {
                                if( $this->_debug )
                                {
                                        JLog::add( $e->getMessage(), JLog::ERROR );
                                }
                        }  
                        
		}
                
                
                if( !( isset( $this->_config['afterQueryOff'] ) && $this->_config['afterQueryOff'] ) )
                {
                        $this->{$this->_checkMethod( 'afterQuery', '__afterQuery' )}();
                }
                
                if( isset( $this->_config['getCacheValues'] ) )
                {
                        if( is_array( $this->_config['getCacheValues'] ) )
                        {
                                // <TODO> rozwi¹zanie: sprawdzaæ w while czy metoda istnieje je¿eli nie to nic nie wykonywaæ
                                // nastêpnie konfiguracjê danej funkcji mergowaæ abyœmy mieli na bierz¹co nowe funkcje
                                // array_mege( $this->_config, $this->_config['getValuesElements']['_nazwafunkcji'])
                                $this->_config['_temp_getCacheValues'] = $this->_config['getCacheValues'];
                                
                                unset( $this->_config['getCacheValues'] );
                                
                                while( $valuesConfig = array_shift( $this->_config['_temp_getCacheValues'] ) )
                                {
                                        $getCacheValues = JArrayHelper::getValue( $valuesConfig, 'getCacheValues', false );
                                        
                                        if( is_string( $getCacheValues ) && method_exists( $this, $getCacheValues ) )
                                        {
                                                $this->_config = array_merge( $this->_config, $valuesConfig );
                                                
                                                $this->$getCacheValues();
                                        }
                                }
                        }
                        elseif( $getCacheValues = $this->_checkMethod( 'getCacheValues', false, true ) )
                        {
                                $this->$getCacheValues();
                        }    
                }
                
                return $this->_returnCache();
        }
        
        protected function _getCacheValues()
        {
                if( !( $this->{$this->_checkMethod( 'preparationVarsValues', '__preparationVarsValues', true )}() ) )
                {
                        return;
                }
                
                $this->{$this->_checkMethod( 'searchValuesElements', '__searchValuesElements', true )}();
                
                // If array extensions type ids isn't empty, we get elements from database
		if( $this->{$this->_checkMethod( 'testQueryVarsValues', '__testQueryVarsValues', true )}() )
		{
                        $query = $this->{$this->_checkMethod( 'getQueryValues', '__getQueryValues', true )}();
			
                        // echo nl2br( $query->dump() );
                        
                        if( $this->_debug )
                        {
                                JLog::add( 'Query: ' . (string) $query );
                        }
                        
                        try
                        {
                               // Load result of elemensts
                                if( $_values = $this->_db->setQuery( $query )->loadObjectList() )
                                {
                                        $setDataValue   = $this->_checkMethod( 'setDataValue', '__setDataValue', true );
                                        $addValue       = $this->_checkMethod( 'addValue', '__addValue', true );
                                         
                                        // Add elements to extension type cache
                                        while( $_value = array_shift( $_values ) )
                                        {
                                                $this->$setDataValue( $addValue, $_value );
                                        }
                                }
                        }
                        catch( RuntimeException $e )
                        {
                                if( $this->_debug )
                                {
                                        JLog::add( $e->getMessage(), JLog::ERROR );
                                }
                        } 	
		}
                
                $_vars = $this->_vars->values;
                $_vars->elements = array_diff( $_vars->elements, $_vars->elementsAdd );
                if( !empty( $_vars->elements ) )
                {
                        $addValue = $this->_checkMethod( 'addValue', '_addValue', true );
                        
                        while( $element = array_shift( $_vars->elements ) )
                        {
                                $cache = $this->_cache->$element;
                                $this->$addValue( $cache );
                                
                                if( !$this->_config['elemntsWithoutValues'] )
                                {
                                        $element = $cache->{$this->_vars->elementName};
                                        unset( $this->_cache->{$element} );
                                }
                        }
                }
                
                unset( $_vars );
        }
        
        protected function __preparationVarsValues()
        {
                if( !isset( $this->_config['valuesName'] ) )
                {
                        return false;  
                }
                
                $this->_vars->valuesName                = (string) $this->_config['valuesName'];
                
                $this->_vars->values                    = new stdClass();
                $this->_vars->values->elements          = array();
                $this->_vars->values->elementsAdd       = array();
                
                return true;
        }
        
        protected function __searchValuesElements()
        {
                $elements = get_object_vars( $this->_cache );
                
                $searchValuesElement = $this->_checkMethod( 'searchValuesElement', '__searchValuesElement', true );
                
		while( $element = array_shift( $elements ) )
		{
			$this->$searchValuesElement( $element );
		}
		
		unset( $elements, $element );
        }
        
        protected function __searchValuesElement( &$element )
        {
                $elementName    = $this->_vars->elementName;
                $_vars          = $this->_vars->values;
                // if element don't have filter_connection, add to arrry
                if( !isset( $element->{$this->_vars->valuesName} ) )
                {
                        array_push( $_vars->elements, $element->$elementName );
                }
                // if we need only elements with filter_connections isn't empty
                elseif( !$this->_config['elemntsWithoutValues'] )
                {
                        $_values = get_object_vars( $element->{$this->_vars->valuesName} );
                        
                        if( empty( $_values ) )
                        {
                                unset( $this->_cache->{$element->$elementName} );
                        }
                        
                        unset( $_values );
                }
        }
        
        protected function __testQueryVarsValues()
        {
                return ( !empty( $this->_vars->values->elements ) );
        }
        
        protected function __getQueryValues()
        {
                $query  = $this->_db->getQuery( true );
                
		return $query;
        }
        
        protected function __setDataValue( $addValueMethod, &$_value )
        {
                $_vars = $this->_vars;
                
                if( isset( $_value->{$_vars->elementName} ) && !in_array( $_value->{$_vars->elementName}, $_vars->values->elementsAdd ) )
                {
                        array_push( $_vars->values->elementsAdd, $_value->{$_vars->elementName} );
                }
                
                $this->$addValueMethod( $_value );
        }
        
        protected function __addValue( &$value ){}
        
        protected function _getCachePivot( $pivot, $types, $els = null, $states = null, $getCacheElements = null )
        {
                $methodArgs = func_get_args();
                unset( $methodArgs[0] );
                
                $hash = md5( serialize( $methodArgs ) );

                if( !isset( $this->_pivots[$hash] ) )
                {
                        $getCacheElements = is_string( $getCacheElements ) && method_exists( $this, $getCacheElements ) ? $getCacheElements : '_getCache';
                        
                        $cache = $this->$getCacheElements( $types, $els, $states );
                        
                        $this->_pivots[$hash] = new stdClass();
                        $this->_pivots[$hash]->elements = new JObject( JArrayHelper::pivot( (array) get_object_vars( $cache ), $pivot ) );
                        $this->_pivots[$hash]->_pivot =  $pivot;
                        
                        unset( $cache );
                }
                elseif( $this->_pivots[$hash]->_pivot != $pivot )
                {
                        $cache = (array) get_object_vars( $this->_pivots[$hash]->elements );
                        
                        $this->_pivots[$hash]->elements = new JObject( JArrayHelper::pivot( FieldsandfiltersArrayHelper::flatten( $cache ), $pivot ) );
                        $this->_pivots[$hash]->_pivot =  $pivot;
                }
                
                return $this->_pivots[$hash]->elements;  
        }
        
        protected function _getCacheColumn( $column, $types, $els = null, $states = null, $getCacheElements = null )
        {
                $hash = md5( serialize( func_get_args() ) );

                if( !isset( $this->_columns[$hash] ) )
                {
                        $getCacheElements = is_string( $getCacheElements ) && method_exists( $this, $getCacheElements ) ? $getCacheElements : '_getCache';
                        
                        $cache = $this->$getCacheElements( $types, $els, $states );
                        
                        $this->_columns[$hash] = FieldsandfiltersArrayHelper::getColumn( $cache, $column );
                        
                        unset( $cache );
                }
                
                return $this->_columns[$hash];  
        }
        
        
}