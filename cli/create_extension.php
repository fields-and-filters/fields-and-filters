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

		$xml = simplexml_load_file($path);
		$xml = json_encode($xml);
		$data->set('xml', new JRegistry($xml));

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

		$path = sprintf('%s/%s_%s.%s', $temp, $extension->get('xml')->get('name'), $extension->get('version', $extension->get('xml')->get('version')), $adapter);
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
		$files = array();

		switch(strtolower($xml->get('@attributes.type')))
		{
			case 'component':
				$path = '/components/'.$xml->get('name');
				$files = array_merge($files, $this->getFiles(JPATH_SITE.$path, $xml->get('files')));
				$files = array_merge($files, $this->getFiles(JPATH_ADMINISTRATOR.$path, $xml->get('administration.files')));
				$path = JPath::clean(JPATH_ADMINISTRATOR.$path);
				$langauges = array(
					'languages' => JPATH_SITE,
					'administration.languages' => JPATH_ADMINISTRATOR
				);
				break;
			case 'module':
				$langauges['languages'] = $path = ($xml->get('@attributes.client') == self::ADMINISTRATOR ? JPATH_ADMINISTRATOR : JPATH_SITE);

				$path .= '/modules/'.$xml->get('name');
				$files = array_merge($files, $this->getFiles($path, $xml->get('files')));
				break;
			default:
				throw new InvalidArgumentException(sprintf('Extension type "%s" does not support.', $extension->get('xml')->get('@attributes.type')));
		}

		$files[] = self::getFile($extension->get('path'), $path);

		if ($xml->get('scriptfile') && ($script = JPath::clean($path.'/'.$xml->get('scriptfile'))) && is_file($script))
		{
			$files[] = self::getFile($script, $path);
		}

		if ($media = $xml->get('media'))
		{
			$files = array_merge($files, $this->getFiles(JPATH_ROOT.'/media/'.$xml->get('media.@attributes.destination'), $xml->get('media')));
		}

		foreach($langauges AS $key => $base)
		{
			if ($language = $xml->get($key))
			{
				$files = array_merge($files, $this->getLanguages($base.'/language', $language));
			}
		}

		return $files;
	}

	protected function getFiles($base, $object)
	{
		$files = array();
		$object = new JRegistry($object);
		$base = JPath::clean($base);

		foreach ((array)$object->get('folder') AS $folder)
		{
			if ($paths = JFolder::files($base.'/'.$folder, '.', true, true))
			{
				foreach ((array) $paths AS $file)
				{
					$files[] = self::getFile($file, $base, $object->get('@attributes.folder'));
				}
			}
			else
			{
				$this->out(self::error(sprintf('Folder "%s" is empty or does not exists', $base.'/'.$folder)));
			}
		}

		foreach ((array) $object->get('filename') AS $filename)
		{
			$file = JPath::clean($base.'/'.$filename);

			if (is_file($file))
			{
				$files[] = self::getFile($file, $base, $object->get('@attributes.folder'));
			}
			else
			{
				$this->out(self::error(sprintf('File "%s" does not exists', $file)));
			}
		}

		return $files;
	}

	protected function getLanguages($base, $object)
	{
		$files = array();
		$object = new JRegistry($object);
		$base = JPath::clean($base);

		foreach ((array) $object->get('language') AS $path)
		{
			$filename = basename($path);
			$lang = strstr($filename, '.', true);
			$file = JPath::clean($base.'/'.$lang.'/'.$filename);

			if (is_file($file))
			{
				$files[] = self::getFile($file, $base, $object->get('@attributes.folder').'/'.dirname($path), $lang.'/');
			}
			else
			{
				$this->out(self::error(sprintf('File "%s" does not exists', $file)));
			}
		}

		return $files;
	}

	protected static function getFile($file, $base, $folder = '', $exclude = '')
	{
		$name = $folder ? $folder.'/' : '';
		$name .= trim(str_replace(array($base, $exclude), '', $file), '/');

		return array(
			'name' => JPath::clean($name),
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
