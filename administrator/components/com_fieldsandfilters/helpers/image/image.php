<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.image
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

KextensionsLanguage::load('com_fieldsandfilters');

/**
 * Image Helper
 *
 * @since       1.0.0
 */
class FieldsandfiltersImage
{
	/**
	 * @since       1.0.0
	 */
	protected static $cache_folder = 'fieldsandfilters';

	/**
	 * @since       1.0.0
	 */
	public static function getCacheFolder()
	{
		return JPATH_ROOT . '/cache/' . self::$cache_folder;
	}

	/**
	 * @since       1.0.0
	 */
	public static function createImage($name, JObject $jobject)
	{
		if (FieldsandfiltersFactory::isVersion('>=', 3.2))
		{
			$jimage = new JImage();
		}
		else
		{
			$jimage = new KextensionsJoomlaImageImage();
		}

		$jimage->loadFile(JPath::clean(JPATH_ROOT . '/' . $jobject->get('path')));

		if (!$jimage->isLoaded())
		{
			throw new RuntimeException(JText::_('COM_FIELDSANDFILTERS_ERROR_IMAGE_FILE_NOT_EXIST'));
		}

		// If the parent folder doesn't exist we must create it
		$folder = JPath::clean(self::getCacheFolder() . '/' . $jobject->get('folder'));
		$src    = '';

		try
		{
			if (!($isFolder = is_dir($folder)))
			{
				jimport('joomla.filesystem.folder');
				$isFolder = JFolder::create($folder);
			}

			if ($isFolder)
			{
				$width  = (int) $jobject->get('width');
				$height = (int) $jobject->get('height');
				$method = (int) $jobject->get('method');

				if (!$width || !$height)
				{
					throw new UnexpectedValueException(JText::sprintf('COM_FIELDSANDFILTERS_ERROR_UNEXPECTED_VALUE_WIDTH_OR_HEIGHT', $name));
				}
				else
				{
					switch ($method)
					{
						// Generate cropping resize image
						case $jimage::CROP_RESIZE:
							$jimage->cropResize($width, $height, null, null, false);
							break;
						// Generate cropping image
						case $jimage::CROP:
							$jimage->crop($width, $height, null, null, false);
							break;
						// Generate resizing image
						case $jimage::SCALE_FILL:
						case $jimage::SCALE_INSIDE:
						case $jimage::SCALE_OUTSIDE:
						case $jimage::SCALE_FIT:
						default:
							$jimage->resize($width, $height, false, $method);
							break;
					}

					// Parent image properties
					$properties = $jimage::getImageFileProperties($jimage->getPath());
					$quality    = (int) $jobject->get('quality', 75);
					$quality    = (int) min(max($quality, 0), 100);

					if ($properties->type == IMAGETYPE_PNG)
					{
						$quality = (int) min(max(floor($quality / 10), 0), 9);
						$quality = abs(9 - $quality);
					}

					if (!($name = $jobject->get('name')))
					{
						$name = self::createNameImage($jobject);
					}

					$imagePath = JPath::clean($folder . '/' . JFile::makeSafe($name));

					// Save image file to disk
					$jimage->toFile($imagePath, $properties->type, array('quality' => $quality));

					if (is_file($imagePath))
					{
						$src = str_replace(JPath::clean(JPATH_ROOT . '/'), '', $imagePath);
					}
				}
			}
		} catch (Exception $e)
		{
			$jimage->destroy();
			throw new Exception($e->getMessage());
		}

		$jimage->destroy();

		return $src;
	}

	public static function createNameImage(JObject $jobject)
	{
		if ($path = $jobject->get('path'))
		{
			$filename      = pathinfo($path, PATHINFO_FILENAME);
			$fileExtension = pathinfo($path, PATHINFO_EXTENSION);
			$imageName     = (($prefix = $jobject->get('prefixName')) ? $prefix . '_' . $filename : $filename) . '.' . $fileExtension;

			return JFile::makeSafe($imageName);
		}

		return JFactory::getDate()->toUnix();
	}
}
	