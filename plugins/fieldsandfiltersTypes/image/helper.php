<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.image
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
 * Image Helper
 * @since       1.0.0
 */
class plgFieldsandfiltersTypesImageHelper extends JImage
{
	/**
	* @since       1.0.0
	*/
	protected static $_quality_png = array( 0 => 9, 1 => 8, 2 => 7, 3 => 6, 4 => 5, 5 => 4, 6 => 3, 7 => 2, 8 => 1, 9 => 0 );
        
	/**
	* @since       1.0.0
	*/
        protected static $_cache_folder = 'cache/fieldsandfilters';
        
	/**
	* @since       1.0.0
	*/
        public static function getCacheFolder()
        {
                return self::$_cache_folder;
        }
        
	/**
	* @since       1.0.0
	*/
        public static function createImage( JObject $jobject )
	{
		jimport( 'joomla.filesystem.file' );
		
		$jroot 	= JPATH_ROOT . '/';
		$return = false;
		
		$jimage = new plgFieldsandfiltersTypesImageHelper();
		$jimage->loadFile( JPath::clean( $jroot . $jobject->get( 'path' ) ) );
		
		if( !$jimage->isLoaded() )
		{
			throw new RuntimeException( JText::_( 'PLG_FAF_TS_IE_ERROR_IMAGE_FILE_NOT_EXIST' ) );
			return $return;
		}
		
		// If the parent folder doesn't exist we must create it
		$folder = JPath::clean( $jroot . self::$_cache_folder . '/' . $jobject->get( 'folder' ) );
		
		if( !( $isFolder = is_dir( $folder ) ) )
		{
			jimport( 'joomla.filesystem.folder' );
			$isFolder = JFolder::create( $folder );
		}
			
		if( $isFolder )
		{
			$width 	= (int) $jobject->get( 'width' );
			$height = (int) $jobject->get( 'height' );
			$method = (int) $jobject->get( 'method' );
			
			if( $width && $height )
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
					$quality = (int) JArrayHelper::getValue( self::$_quality_png, $quality, 0 );
				}
				
				if( !( $name = $jobject->get( 'name' ) ) )
				{
					self::_createNameImage( $jobject );
					$name = $jobject->get( 'name' );
				}
				
				$imagePath = JPath::clean( $folder . '/' . JFile::makeSafe( $name ) ) ;
				
				// Save image file to disk
				$jimage->toFile( $imagePath, $properties->type, array( 'quality' => $quality ) );
				
				if( file_exists( $imagePath ) )
				{
					$jobject->src = $imagePath;
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
			jimport( 'joomla.filesystem.file' );
			
			$filename 	= pathinfo( $path, PATHINFO_FILENAME );
			$fileExtension 	= pathinfo( $path, PATHINFO_EXTENSION );
			$imageName 	= ( ( $prefix = $jobject->get( 'prefixName' ) ) ? $prefix . '_' . $filename : $filename ) . '.' . $fileExtension;
			
			$jobject->name 	= JFile::makeSafe( $imageName );
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Method to crop the current image.
	 *
	 * @param   mixed    $width      The width of the image section to crop in pixels or a percentage.
	 * @param   mixed    $height     The height of the image section to crop in pixels or a percentage.
	 * @param   integer  $left       The number of pixels from the left to start cropping.
	 * @param   integer  $top        The number of pixels from the top to start cropping.
	 * @param   bool     $createNew  If true the current image will be cloned, cropped and returned; else
	 *                               the current image will be cropped and returned.
	 *
	 * @return  JImage
	 *
	 * @since       1.0.0
	 * @throws  LogicException
	 */
	public function crop($width, $height, $left = null, $top = null, $createNew = true)
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// Sanitize width.
		$width = $this->sanitizeWidth($width, $height);

		// Sanitize height.
		$height = $this->sanitizeHeight($height, $width);

		// Autocrop offsets
		if (is_null($left))
		{
			$left = round(($this->getWidth() - $width) / 2);
		}
		if (is_null($top))
		{
			$top = round(($this->getHeight() - $height) / 2);
		}

		// Sanitize left.
		$left = $this->sanitizeOffset($left);

		// Sanitize top.
		$top = $this->sanitizeOffset($top);

		// Create the new truecolor image handle.
		$handle = imagecreatetruecolor($width, $height);

		// Allow transparency for the new image handle.
		imagealphablending($handle, false);
		imagesavealpha($handle, true);

		if ($this->isTransparent())
		{
			// Get the transparent color values for the current image.
			$rgba = imageColorsForIndex($this->handle, imagecolortransparent($this->handle));
			$color = imageColorAllocate($this->handle, $rgba['red'], $rgba['green'], $rgba['blue']);

			// Set the transparent color values for the new image.
			imagecolortransparent($handle, $color);
			imagefill($handle, 0, 0, $color);

			imagecopyresized($handle, $this->handle, 0, 0, $left, $top, $width, $height, $width, $height);
		}
		else
		{
			imagecopyresampled($handle, $this->handle, 0, 0, $left, $top, $width, $height, $width, $height);
		}

		// If we are cropping to a new image, create a new JImage object.
		if ($createNew)
		{
			// @codeCoverageIgnoreStart
			$new = new JImage($handle);

			return $new;

			// @codeCoverageIgnoreEnd
		}
		// Swap out the current handle for the new image handle.
		else
		{
			$this->handle = $handle;

			return $this;
		}
	}
	
	/**
	 * Method to destroy an image handle and
	 * free the memory associated with the handle
	 *
	 * @return  boolean  True on success, false on failure or if no image is loaded
	 *
	 * @since       1.0.0
	 */
	public function destroy()
	{
		if ($this->isLoaded())
		{
			return imagedestroy($this->handle);
		}

		return false;
	}

	/**
	 * Method to call the destroy() method one last time
	 * to free any memory when the object is unset
	 *
	 * @see     JImage::destroy()
	 * @since       1.0.0
	 */
	public function __destruct()
	{
		$this->destroy();
	}
}
	