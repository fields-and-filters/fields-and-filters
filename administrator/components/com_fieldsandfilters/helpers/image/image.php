<?php
/**
 * @version     1.1.1
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.image
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

jimport( 'joomla.filesystem.file' );

KextensionsLanguage::load( 'com_fieldsandfilters' );

/**
 * Image Helper
 * @since       1.0.0
 */
class FieldsandfiltersImage
{
	/**
	* @since       1.0.0
	*/
	protected static $quality_png = array( 0 => 9, 1 => 8, 2 => 7, 3 => 6, 4 => 5, 5 => 4, 6 => 3, 7 => 2, 8 => 1, 9 => 0 );
        
	/**
	* @since       1.0.0
	*/
        protected static $cache_folder = 'cache/fieldsandfilters';
        
	/**
	* @since       1.0.0
	*/
        public static function getCacheFolder()
        {
                return self::$cache_folder;
        }
        
	/**
	* @since       1.0.0
	*/
        public static function createImage( $name, JObject $jobject )
	{
		$jroot 	= JPATH_ROOT . '/';
		
		if( FieldsandfiltersFactory::isVersion( '>=', 3.2  ) )
		{
			$jimage = new JImage();
		}
		else
		{
			$jimage = new KextensionsJoomlaImage();
		}
		
		$jimage->loadFile( JPath::clean( $jroot . $jobject->get( 'path' ) ) );
		
		if( !$jimage->isLoaded() )
		{
			throw new RuntimeException( JText::_( 'COM_FIELDSANDFILTERS_ERROR_IMAGE_FILE_NOT_EXIST' ) );
			return false;
		}
		
		// If the parent folder doesn't exist we must create it
		$folder = JPath::clean( $jroot . self::$cache_folder . '/' . $jobject->get( 'folder' ) );
		
		if( !( $isFolder = is_dir( $folder ) ) )
		{
			jimport( 'joomla.filesystem.folder' );
			$isFolder = JFolder::create( $folder );
		}
		
		$return = false;	
		if( $isFolder )
		{
			$width 	= (int) $jobject->get( 'width' );
			$height = (int) $jobject->get( 'height' );
			$method = (int) $jobject->get( 'method' );
			
			if( !$width || !$height )
			{
				throw new UnexpectedValueException( JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_UNEXPECTED_VALUE_WIDTH_OR_HEIGHT', $name ) );
			}
			else
			{
				// Generate cropping image
				if( $method == 4 )
				{
					$jimage->crop( $width, $height, null, null, false );
					
				}
				// Generate resizing image
				else
				{
					$jimage->resize( $width, $height, false, $method );
				}
				
				// Parent image properties
				$properties 	= $jimage::getImageFileProperties( $jimage->getPath() );
				$quality 	= (int) $jobject->get( 'quality', 75 );
				$quality 	= (int) min( max( $quality, 0 ), 100 );
				
				if( $properties->type == IMAGETYPE_PNG )
				{
					$quality = max( floor( ( $quality - 1 ) / 10 ), 0 );
					$quality = (int) JArrayHelper::getValue( self::$quality_png, $quality, 0 );
				}
				
				if( !( $name = $jobject->get( 'name' ) ) )
				{
					$name = self::createNameImage( $jobject );
				}
				
				$imagePath = JPath::clean( $folder . '/' . JFile::makeSafe( $name ) ) ;
				
				// Save image file to disk
				$jimage->toFile( $imagePath, $properties->type, array( 'quality' => $quality ) );
				
				if( is_file( $imagePath ) )
				{
					$object->set( 'src', $imagePath );
					$return = true;
				}
			}
		}
		
		$jimage->destroy();
		
		return $return;
	}
        
        public static function createNameImage( JObject $jobject )
	{
		if( $path = $jobject->get( 'path' ) )
		{
			$filename 	= pathinfo( $path, PATHINFO_FILENAME );
			$fileExtension 	= pathinfo( $path, PATHINFO_EXTENSION );
			$imageName 	= ( ( $prefix = $jobject->get( 'prefixName' ) ) ? $prefix . '_' . $filename : $filename ) . '.' . $fileExtension;
			
			return JFile::makeSafe( $imageName );
		}
		
		return JFactory::getDate()->toUnix();
	}
}
	