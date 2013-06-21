<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */


defined( 'JPATH_PLATFORM' ) or die;

/**
 * FieldsandfiltersExtensionsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
class FieldsandfiltersExtensionsHelper
{
	protected static $_path = array( 'plugins' => array() );
	
	public static function loadPluginTemplate( $plugin, $layout = 'default', $prefixLayout = 'field' )
	{
		$type = $plugin->get( 'type' );
		$name = $plugin->get( 'name' );
		
		// Create the template file name based on the layout
		$file 		= ( !is_null( $prefixLayout ) ? $prefixLayout . '_' . $layout : $layout ) . '.php';
		$extension 	= 'plg_' . $type . '_' . $name . '-' . $file;
		
		jimport('joomla.filesystem.path');
		if( is_null( $template = self::_getPath( 'plugins', $extension ) ) )
		{
			$paths 		= array();
			$paths[] 	= JPATH_PLUGINS . '/' . $type . '/' . $name . '/tmpl';
			$paths[] 	= JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/' . $extension;
			
			$template 	= JPath::find( $paths, $file );
			
			self::_setPath( 'plugins', $extension, $template );
			
			// Unset so as not to introduce into template scope
			unset( $paths );
		}
		
		if( $template != false)
		{
			// Unset so as not to introduce into template scope
			unset( $file, $extension, $type, $name );
			
			// Start capturing output into a buffer
			ob_start();
			
			// Include the requested template filename in the local scope
			// (this will execute the view logic).
			include $template;
			
			// Done with the requested template; get the buffer and
			// clear it.
			$output = ob_get_contents();
			ob_end_clean();
			
			return $output;
		}
	}
	
	protected static function _setPath( $type, $extension, $path )
	{
		self::$_path[$type][$extension] = $path;
	}
	
	protected static function _getPath( $type, $extension )
	{
		return JArrayHelper::getValue( self::$_path[$type], $extension );
	}
	
	/**
	 * Returns a Controller object, always creating it
	 *
	 * @param   string  $type    The contlorer type to instantiate
	 * @param   string  $prefix  Prefix for the controller class name. Optional.
	 * @param   array   $config  Configuration array for controller. Optional.
	 *
	 * @return  mixed   A model object or false on failure
	 *
	 * @since   12.2
	 */
	public static function getControllerInstance( $type, $prefix = '', $config = array() )
	{
		$type 			= preg_replace( '/[^A-Z0-9_\.-]/i', '', $type );
		$prefix 		= preg_replace( '/[^A-Z0-9_]/i', '', $prefix );
		$controllerClass 	= $prefix . ucfirst( $type );
		
		if( !class_exists( $controllerClass ) )
		{
			if( is_null( self::_getPath( 'controller', $controllerClass ) ) )
			{
				jimport( 'joomla.filesystem.path' );
				
				// Get the environment configuration.
				$basePath = JArrayHelper::getValue( $config, 'base_path', JPATH_COMPONENT );
				$nameConfig = empty( $type ) ? array( 'name' => 'controller' ) : array( 'name' => $type, 'format' => JFactory::getApplication()->input->get( 'format', '', 'word' ) );
				
				// Define the controller path.
				$paths 		= array();
				$paths[] 	= $basePath . '/controllers';
				$paths[] 	= $basePath;
				
				$path = JPath::find( $paths, self::_createFileName( 'controller', $nameConfig ) );
				self::_setPath( 'controller', $controllerClass, $path );
				
				// If the controller file path exists, include it.
				if( $path )
				{
					require_once $path;
				}
			}
			
			if( !class_exists( $controllerClass ) )
			{
				JLog::add( JText::sprintf( 'JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $controllerClass ), JLog::WARNING, 'jerror' );
				return false;
			}
		}

		return new $controllerClass( $config );
	}

	
	/**
	 * Create the filename for a resource.
	 *
	 * @param   string  $type   The resource type to create the filename for.
	 * @param   array   $parts  An associative array of filename information. Optional.
	 *
	 * @return  string  The filename.
	 *
	 * @since   12.2
	 */
	protected static function _createFileName( $type, $parts = array() )
	{
		$filename = '';

		switch( $type )
		{
			case 'controller':
				if( !empty($parts['format'] ) )
				{
					if ($parts['format'] == 'html')
					{
						$parts['format'] = '';
					}
					else
					{
						$parts['format'] = '.' . $parts['format'];
					}
				}
				else
				{
					$parts['format'] = '';
				}

				$filename = strtolower( $parts['name'] . $parts['format'] . '.php' );
			break;
		}

		return $filename;
	}
	
	static function loadLanguage( $extension, $basePath = JPATH_BASE )
	{
		$lang 		= JFactory::getLanguage();
		$type 		= strtolower( substr( $extension, 0, 3 ) );
		
		switch( $type )
		{
			case 'com' :
				$path = JPATH_BASE . '/components/' . $extension;
			break;
			case 'mod' :
				$path = JPATH_BASE . '/modules/' . $extension;
			break;
			case 'plg' :
				list( , $type, $name ) = explode( '_', $extension, 3 );
				$path = JPATH_PLUGINS . '/' . $type . '/' . $name;
			break;
			default :
				return;
			break;
		}
		
		return $lang->load( $extension, $basePath, null, false, false )
			|| $lang->load( $extension, $basePath, $lang->getDefault(), false, false )
			|| $lang->load( $extension, $path, null, false, false )
			|| $lang->load( $extension, $path, $lang->getDefault(), false, false );
	}
}