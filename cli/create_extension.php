<?php
/**
 * @version     1.0.0
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

jimport('joomla.filesystem.path');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

class CreateExtensionCli extends JApplicationCli
{
	const ADMINISTRATOR = 'administrator';

	protected static $error = array(
		'foregroundColors' => 37,
		'backgroundColors' => 41
	);

	public function doExecute()
	{
		try
		{
			$this->creates();
		}
		catch (Exception $e)
		{
			$this->out(self::error($e->getMessage()));
		}
	}

	protected function creates()
	{
		if (empty($this->input->args))
		{
			throw new InvalidArgumentException('Empty path file arguments');
		}

		foreach($this->input->args AS $path) {
			$this->create($path);
		}
	}

	protected function create($path)
	{
		if (strtolower(JFile::getExt($path)) != 'json')
		{
			throw new UnexpectedValueException(sprintf('Path "%s" must have json extension', $path));
		}

		$path = JPath::clean(JPATH_ROOT.'/'.$path);

		if (!is_file($path))
		{
			throw new Exception(sprintf('File "%s" does not exists', $path));
		}

		$extension = self::getExtension($path);

		$this->cloneExtension($extension);
	}

	protected static function getExtension($json)
	{
		$data = new JRegistry();
		$data->loadFile($json);

		$path = $data->get('xml');
		if (!$path)
		{
			throw new InvalidArgumentException(sprintf('Property "xml" does not exists in "%s"', $json));
		}

		$path = JPath::clean(sprintf('%s/%s.xml', JPATH_ROOT, $path));

		if (!is_file($path))
		{
			throw new Exception(sprintf('File "%s" does not exists.', $path));
		}

		$data->set('path', $path);
		$data->set('xml', simplexml_load_file($path));

		return $data;
	}

	protected function cloneExtension(JRegistry $extension)
	{
		$temp = JPath::clean($this->get('tmp_path'));

		if (!is_dir($temp))
		{
			throw new Exception(sprintf('Dir "%s" does not exists.', $temp));
		}

		if (!is_writable($temp))
		{
			throw new Exception(sprintf('Dir "%s" does not writable.', $temp));
		}

		$adapter = $this->input->get('adapter', 'zip');

		$path = sprintf('%s/%s_%s.%s', $temp, (string) $extension->get('xml')->name, $extension->get('version', (string) $extension->get('xml')->version), $adapter);
		$path = JPath::clean($path);

		$files =$this->_getFiles($extension);

		if (!$this->input->getBool('not-archive'))
		{
			if (!JArchive::getAdapter($adapter)->create($path, $files))
			{
				throw new Exception(sprintf('File "%s" does not crated.', $path));
			}
		}

		if ($this->input->get('list'))
		{
			foreach($files AS $file)
			{
				$this->out($file['name']);
			}
		}
	}

	protected function _getFiles(JRegistry $extension)
	{
		$xml = $extension->get('xml');

		switch(strtolower((string) $xml->attributes()->type))
		{
			case 'component':
				$path = '/components/'.(string) $xml->name;
				$files = $this->getFiles(JPATH_SITE.$path, $xml->files);
				$files = array_merge($files, $this->getFiles(JPATH_ADMINISTRATOR.$path, $xml->administration->files));
				$path = JPath::clean(JPATH_ADMINISTRATOR.$path);
				$langauges = array(
					JPATH_SITE => $xml->languages,
					JPATH_ADMINISTRATOR => $xml->administration->languages
				);
				break;
			case 'module':
				$path = ((string) $xml->attributes()->client == self::ADMINISTRATOR ? JPATH_ADMINISTRATOR : JPATH_SITE);
				$langauges[$path] = $xml->languages;

				$path .= '/modules/'.(string) $xml->name;
				$files = $this->getFiles($path, $xml->files);
				break;
			case 'plugin':
				$langauges[JPATH_ADMINISTRATOR] = $xml->languages;

				$path = JPATH_SITE.'/plugins/'.(string) $xml->attributes()->group.'/'.$xml->files->filename->attributes()->plugin;
				$files = $this->getFiles($path, $xml->files);
				break;
			default:
				throw new InvalidArgumentException(sprintf('Extension type "%s" does not support.', (string) $xml->attributes()->type));
		}

		$files[] = self::getFile($extension->get('path'), $path);

		if (isset($xml->scriptfile) && ($script = JPath::clean($path.'/'.(string) $xml->scriptfile)) && is_file($script))
		{
			$files[] = self::getFile($script, $path);
		}

		if (isset($xml->media))
		{
			$files = array_merge($files, $this->getFiles(JPATH_ROOT.'/media/'.(string) $xml->media->attributes()->destination, $xml->media));
		}

		foreach($langauges AS $base => $object)
		{
			$files = array_merge($files, $this->getLanguages($base.'/language', $object));
		}

		return $files;
	}

	protected function getFiles($base, SimpleXMLElement $object)
	{
		$files = array();
		$base = JPath::clean($base);

		if (isset($object->folder))
		{
			foreach ($object->folder AS $folder)
			{
				if ($paths = JFolder::files($base.'/'.(string) $folder, '.', true, true))
				{
					foreach ((array) $paths AS $file)
					{
						$files[] = self::getFile($file, $base, (string) $object->attributes()->folder);
					}
				}
				else
				{
					$this->out(self::error(sprintf('Folder "%s" is empty or does not exists', $base.'/'.(string) $folder)));
				}
			}
		}

		if (isset($object->filename))
		{
			foreach ($object->filename AS $filename)
			{
				$file = JPath::clean($base.'/'.(string) $filename);

				if (is_file($file))
				{
					$files[] = self::getFile($file, $base, (string) $object->attributes()->folder);
				}
				else
				{
					$this->out(self::error(sprintf('File "%s" does not exists', $file)));
				}
			}
		}

		return $files;
	}

	protected function getLanguages($base, SimpleXMLElement $object)
	{
		$files = array();
		$base = JPath::clean($base);

		if (isset($object->language))
		{
			foreach ($object->language AS $path)
			{
				$filename = basename((string) $path);
				$file = JPath::clean($base.'/'.(string) $path->attributes()->tag.'/'.$filename);

				if (is_file($file))
				{
					$files[] = self::getFile($file, $base, (string) $object->attributes()->folder, (string) $path);
				}
				else
				{
					$this->out(self::error(sprintf('File "%s" does not exists', $file)));
				}
			}
		}

		return $files;
	}

	protected static function getFile($file, $base, $folder = '', $name = null)
	{
		$name = is_string($name) ? $name : trim(str_replace($base, '', $file), '/');

		return array(
			'name' => JPath::clean(($folder ?: '').'/'.$name),
			'data' => file_get_contents($file)
		);
	}


	protected static function error($text)
	{
		return sprintf("\033[%sm%s\033[0m", implode(';', self::$error), $text);
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('CreateExtensionCli')->execute();
