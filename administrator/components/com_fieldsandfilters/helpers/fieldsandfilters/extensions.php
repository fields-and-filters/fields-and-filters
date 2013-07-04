<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */
defined( 'JPATH_PLATFORM' ) or die;

jimport('joomla.filesystem.path');

/**
 * FieldsandfiltersExtensionsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
class FieldsandfiltersExtensionsHelper
{
	protected static $_path = array( 'plugins' => array() );
	
	/**
	 * @since       1.1.0
	 */
	public static function loadPluginTemplate( $plugin, $layout = 'default' )
	{
		$type = $plugin->get( 'type' );
		$name = $plugin->get( 'name' );
		
		// Create the plugin name
		$extension 	= 'plg_' . $type . '_' . $name;
		
		if( $template = self::_getPath( 'plugins', $extension, $layout ) )
		{
			$template = self::_getLayoutPath( $type, $name, $layout );
			
			self::_setPath( $template. 'plugins', $extension, $layout );
		}
		
		// Unset so as not to introduce into template scope
		unset( $extension, $type, $name );
		
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
	
	protected static function _getLayoutPath( $type, $name, $layout = 'default' )
	{
		if( FieldsandfiltersFactory::isVersion() )
		{
			return JPluginHelper::getLayoutPath( $type, $name, $layout );
		}
		else
		{
			$template = JFactory::getApplication()->getTemplate();
			$defaultLayout = $layout;
			
			if (strpos($layout, ':') !== false)
			{
				// Get the template and file name from the string
				$temp = explode(':', $layout);
				$template = ($temp[0] == '_') ? $template : $temp[0];
				$layout = $temp[1];
				$defaultLayout = ($temp[1]) ? $temp[1] : 'default';
			}
			
			// Build the template and base path for the layout
			$tPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $type . '_' . $name . '/' . $layout . '.php';
			$bPath = JPATH_BASE . '/plugins/' . $type . '/' . $name . '/tmpl/' . $defaultLayout . '.php';
			$dPath = JPATH_BASE . '/plugins/' . $type . '/' . $name . '/tmpl/default.php';
			
			// If the template has a layout override use it
			if (file_exists($tPath))
			{
				return $tPath;
			}
			elseif (file_exists($bPath))
			{
				return $bPath;
			}
			else
			{
				return $dPath;
			}
		}
	}
	
	/**
	 * @since       1.1.0
	 */
	protected static function _setPath( $path, $type, $extension, $layout = 'default' )
	{
		self::$_path[$type][$extension][$layout] = $path;
	}
	
	/**
	 * @since       1.1.0
	 */
	protected static function _getPath( $type, $extension, $layout  = 'default' )
	{
		return ( isset( self::$_path[$type][$extension][$layout] ) ? self::$_path[$type][$extension][$layout] : null ) ;
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
	 * @since       1.1.0
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
				// Get the environment configuration.
				$basePath = JArrayHelper::getValue( $config, 'base_path', JPATH_COMPONENT );
				$nameConfig = empty( $type ) ? array( 'name' => 'controller' ) : array( 'name' => $type, 'format' => JFactory::getApplication()->input->get( 'format', '', 'word' ) );
				
				// Define the controller path.
				$paths[] 	= $basePath . '/controllers';
				$paths[] 	= $basePath;
				
				$path = JPath::find( $paths, self::_createFileName( 'controller', $nameConfig ) );
				self::_setPath( $path, 'controller', $controllerClass );
				
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
	 * @since       1.0.0
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
	
	/**
	 * @since       1.0.0
	 */
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
			case 'tpl' :
				$path = JPATH_BASE . '/templates/' . $extension;
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