<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */


defined( 'JPATH_PLATFORM' ) or die;

jimport( 'joomla.utilities.arrayhelper' );

/**
 * FieldsandfiltersXMLHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
class FieldsandfiltersXMLHelper
{
        /**
	 * Adds a new child SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement  $source  The source element on which to append.
	 * @param   SimpleXMLElement  $new     The new element to append.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception if an error occurs.
	 */
	protected static function addNode( SimpleXMLElement $source, SimpleXMLElement $new )
	{
		// Add the new child node.
		$node = $source->addChild( $new->getName(), trim( $new ) );
		
		// Add the attributes of the child node.
		foreach( $new->attributes() as $name => $value )
		{
			$node->addAttribute( $name, $value );
		}

		// Add any children of the new node.
		foreach( $new->children() as $child )
		{
			self::addNode($node, $child);
		}
	}
        
        /**
	 * Method to set some field XML elements to the source xml. 
	 *
	 * @param   SimpleXMLElement            $source         The source element on which to append.
	 * @param   array/SimpleXMLElement      &$elements      The array of XML element object representations of the form fields Or single xml element.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
        
        public static function setFields( &$sorce, &$elements )
        {
                if( is_array( $elements ) )
                {
                        foreach( $elements AS $element )
                        {
                                if( $element instanceof SimpleXMLElement )
                                {
                                        self::addNode( $sorce, $element );
                                }
                        }
			
			return true;
                }
                elseif( $elements instanceof SimpleXMLElement )
                {
                        self::addNode( $sorce, $elements );
			
			return true;
                }
		
		return false;
        }
	
	/**
	 * Adds a new options SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement  $source  The source element on which to append.
	 * @param   SimpleXMLElement  $new     The new element to append.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function mergeOptionsNode( SimpleXMLElement $source, SimpleXMLElement $new )
	{
		if( isset( $new->option ) )
		{
			// Update the options of the child node.
			foreach( $new->option AS $option )
			{
				$newOption = $source->addChild( 'option', (string) $option[0] );
				$newOption->addAttribute( 'value', (string) $option['value'] );
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Adds a new options SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement  	$source  	The source element on which to append.
	 * @param   array	  	$options     	The associative array of options.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function addOptionsNode( SimpleXMLElement $source, $options = array(), $flip = false )
	{
		if( !is_array( $options ) || empty( $options ) )
		{
			return false;
		}
		
		if( $flip )
		{
			$options = array_flip( $options );
		}
		
		foreach( $options AS $key => $value )
		{
			$option = $source->addChild( 'option', $key );
			$option->addAttribute( 'value', $value );
		}
		
		return true;
	}
	
	/**
	 * Method to get the options type form xml.
	 * @param       string  	$folder 	Folder name of plugin
	 * @param       string  	$element 	Element name of plugin
	 * @param       string  	$fileName 	File name of xml plugin
	 *      
	 * @return	boolean		return true if succes.
	 * @since	1.0.0
	 */
	public static function getOptionsFromXML( $element, $config = array() )
	{
		jimport( 'joomla.filesystem.file' );
                
                $fileName = isset( $config['fileName'] ) ? $config['fileName'] : 'params';
                
		// Get the params file path plugin.
		if( property_exists( $element, 'file_path' ) )
		{
			$filePath = $element->file_path;
		}
		else
		{
			$filePath = JPATH_PLUGINS . '/' . $element->type . '/' . $element->name . '/forms/' . $fileName . '.xml';
		}
		
                $filePath = JPath::clean( $filePath );  
                
		if( !is_file( $filePath ) )
		{
			return;
		}
                
		$element->title		= ucfirst( $element->name );
		$element->description	= '';
		$element->xml		= array( 'params' => $filePath );
		$element->group		= array( 'type' => 'others', 'title' => 'COM_FIELDSANDFILTERS_PLUGINSTYPES_OTHERS', 'description' => 'COM_FIELDSANDFILTERS_PLUGINSTYPES_OTHERS_DESC' );
                
		// Attempt to load the xml file.
		if( $xml = simplexml_load_file( $filePath ) )
		{
			// Look for the first view node off of the root node.
			if ( $layout = $xml->xpath( 'layout[1] ') )
			{
				$layout = $layout[0];
				
				// Get group if params have it
				if( $group = $xml->xpath( 'group[1]' ) )
				{
					$group = $group[0];
					if( !( empty( $group['type'] ) && empty( $group['title'] ) ) )
					{
						$element->group['type'] = trim( (string) $group['type'] );
						
						$element->group['title'] = trim( (string) $group['title'] );
						
						$element->group['description'] = ( !empty( $group->message[0] ) ? trim( (string) $group->message[0] ) : '' );
					}
				}
		
				// If the view is hidden from the menu, discard it and move on to the next view.
				if( !empty( $layout['hidden'] ) && $layout['hidden'] == 'true' )
				{
					unset( $xml );
					return null;
				}

				// Populate the title and description if they exist.
				if( !empty( $layout['title'] ) )
				{
					$element->title = trim( (string) $layout['title'] );
				}

				if( !empty( $layout->message[0] ) )
				{
					$element->description = trim( (string) $layout->message[0] );
				}
			}
		}
                
                return true;
	}
}
