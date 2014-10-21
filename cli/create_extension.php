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
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
    require_once JPATH_LIBRARIES . '/import.legacy.php';
}
else // Joomla! 2.5
{
    require_once JPATH_LIBRARIES . '/import.php';
}

// Bootstrap the CMS libraries.
if (file_exists(JPATH_LIBRARIES . '/cms.php'))
{
    require_once JPATH_LIBRARIES . '/cms.php';
}
else // Joomla! 2.5
{
    require_once JPATH_BASE . '/includes/framework.php';
}

jimport('joomla.filesystem.path');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

if (version_compare(JVERSION, 3.0, '<'))
{
    jimport('joomla.filesystem.archive');
}

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

class CreateExtensionCli extends JApplicationCli
{
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

		$data = self::loadJSON($path);
		$xml = (array) $data->get('xml');

		foreach ($xml AS $path)
		{
			$this->out('-------- Start Archive --------');

			$extension = $this->getExtension($path, $data->get('info'));
			$this->archiveExtension($extension);

			$this->out('-------- End Archive --------');
		}
	}

	protected static function loadJSON($path)
	{
		$data = new JRegistry();
		$data->loadFile($path);

		return $data;
	}

	protected function getExtension($path, $info = null)
	{
		if (!$path)
		{
			throw new InvalidArgumentException('Property "path" is empty');
		}

		$path = JPath::clean(sprintf('%s/%s.xml', JPATH_ROOT, $path));

		if (!is_file($path))
		{
			throw new Exception(sprintf('File "%s" does not exists.', $path));
		}

		$data = new JObject();
		$data->set('path', $path);
		$data->set('info', $info);
		$data->set('xml', simplexml_load_file($path));
		$this->changeInfoXml($data, $info);

		$this->out('Extension: '.(string) $data->get('xml')->name);

		return $data;
	}

	protected function changeInfoXml(JObject $extension, $info = null)
	{
		if (!is_object($info))
		{
			return;
		}

		$xml = $extension->get('xml');

		foreach ($info AS $name => $value)
		{
			if (isset($xml->$name))
			{
				$xml->$name = $value;
			}
		}

		$xml->saveXML($extension->get('path'));
	}

	protected function archiveExtension(JObject $extension)
	{
		$temp = $this->getTempPath();

		$files = $this->_getFiles($extension);

		if (!(boolean) $this->input->get('not-archive'))
		{
			$adapter = $this->input->get('adapter', 'zip');
			$xml = $extension->get('xml');

			if ($extension->get('archive'))
			{
				$path = $extension->get('archive');
			}
			else
			{
				$name = $extension->get('name', (string) $xml->name);
				$path = sprintf('%s/%s', $temp, $name);

				if ($this->input->get('with-version'))
				{
					$path .= '_'.$extension->get('version', (string) $xml->version);
				}

				$path .= '.'.$adapter;
			}

			$path = JPath::clean($path);

			if (!JArchive::getAdapter($adapter)->create($path, $files))
			{
				throw new Exception(sprintf('File "%s" does not crated.', $path));
			}

			$extension->set('archive', basename($path));

			$this->out('Created archive: ' . basename($path));
		}

		if ($this->input->get('list'))
		{
			foreach($files AS $file)
			{
				$this->out($file['name']);
			}
		}
	}

	protected function _getFiles(JObject $extension)
	{
		$xml = $extension->get('xml');
		$language = '/language';

		switch(strtolower((string) $xml->attributes()->type))
		{
			case 'component':
				$path = '/components/'.(string) $xml->name;
				$files = $this->getFiles(JPATH_SITE.$path, $xml->files);
				$files = array_merge($files, $this->getFiles(JPATH_ADMINISTRATOR.$path, $xml->administration->files));
				$files = array_merge($files, $this->getLanguages(JPATH_SITE.$language, $xml->languages));
				$files = array_merge($files, $this->getLanguages(JPATH_ADMINISTRATOR.$language, $xml->administration->languages));
				$path = JPath::clean(JPATH_ADMINISTRATOR.$path);
				break;
			case 'module':
				$client = JApplicationHelper::getClientInfo((string) $xml->attributes()->client, true);
				$path = $client->path.'/modules/'.(string) $xml->name;
				$files = $this->getFiles($path, $xml->files);
				$files = array_merge($files, $this->getLanguages($client->path.$language, $xml->languages));
				break;
			case 'plugin':
				$path = JPATH_PLUGINS.'/'.(string) $xml->attributes()->group.'/'.$xml->files->filename->attributes()->plugin;
				$files = $this->getFiles($path, $xml->files);
				$files = array_merge($files, $this->getLanguages(JPATH_ADMINISTRATOR.$language, $xml->languages));
				break;
			case 'library':
				$path = JPATH_LIBRARIES.'/'.$xml->libraryname;
				$files = $this->getFiles($path, $xml->files);
				$files = array_merge($files, $this->getLanguages(JPATH_ADMINISTRATOR.$language, $xml->languages));
				$extension->set('name', 'lib_'.$xml->libraryname);
				break;
			case 'package':
				$path = JPATH_MANIFESTS.'/packages/'.$xml->packagename;
				$files = $this->getLanguages(JPATH_ADMINISTRATOR.$language, $xml->languages);
				$files = array_merge($files,$this->preparePackage($xml->files, $extension->get('info')));
				$extension->set('name', 'pkg_'.$xml->packagename);
				break;
			default:
				throw new InvalidArgumentException(sprintf('Extension type "%s" does not support.', (string) $xml->attributes()->type));
		}

		$files[] = self::getFile($extension->get('path'), dirname($extension->get('path')));

		if ($xml->scriptfile && ($script = JPath::clean($path.'/'.(string) $xml->scriptfile)) && is_file($script))
		{
			$files[] = self::getFile($script, $path);
		}

		if ($xml->media)
		{
			$files = array_merge($files, $this->getFiles(JPATH_ROOT.'/media/'.(string) $xml->media->attributes()->destination, $xml->media));
		}

		return $files;
	}

	protected function preparePackage(SimpleXMLElement $xml, $info = null)
	{
		$files = array();
		$temp = $this->getTempPath();

		if ($xml->file) {
			foreach ($xml->file AS $file)
			{
				$this->out('--- Start Prepare Package ---');

				$attributes = $file->attributes();
				$extension = $this->getExtension(self::getXmlPath((string) $attributes->type, (string) $attributes->id, (string) $attributes->group, (string) $attributes->client), $info);

				$extension->set('archive', $temp.'/'.(string) $file);

				$this->archiveExtension($extension);

				$path = $temp.'/'.$extension->get('archive');

				$files[] = self::getFile($path, $temp);

				JFile::delete($path);

				$this->out('--- End Prepare Package ---');
			}
		}

		return $files;
	}

	protected function getFiles($base, SimpleXMLElement $xml)
	{
		$files = array();
		$base = JPath::clean($base);

		if ($xml->folder)
		{
			foreach ($xml->folder AS $folder)
			{
				if ($paths = JFolder::files($base.'/'.(string) $folder, '.', true, true))
				{
					foreach ((array) $paths AS $file)
					{
						$files[] = self::getFile($file, $base, (string) $xml->attributes()->folder);
					}
				}
				else
				{
					$this->out(self::error(sprintf('Folder "%s" is empty or does not exists', $base.'/'.(string) $folder)));
				}
			}
		}

		if ($xml->filename)
		{
			foreach ($xml->filename AS $filename)
			{
				$file = JPath::clean($base.'/'.(string) $filename);

				if (is_file($file))
				{
					$files[] = self::getFile($file, $base, (string) $xml->attributes()->folder);
				}
				else
				{
					$this->out(self::error(sprintf('File "%s" does not exists', $file)));
				}
			}
		}

		return $files;
	}

	protected function getLanguages($base, SimpleXMLElement $xml)
	{
		$files = array();
		$base = JPath::clean($base);

		if ($xml->language)
		{
			foreach ($xml->language AS $path)
			{
				$filename = basename((string) $path);
				$file = JPath::clean($base.'/'.(string) $path->attributes()->tag.'/'.$filename);

				if (is_file($file))
				{
					$files[] = self::getFile($file, $base, (string) $xml->attributes()->folder, (string) $path);
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
			'name' => JPath::clean(($folder ? '/' : '').$name),
			'data' => file_get_contents(JPath::clean($file))
		);
	}

	protected static function getXmlPath($type, $name, $group = '', $client = '', $reference = true)
	{
		switch($type)
		{
			case 'component':
				$path = JPATH_ADMINISTRATOR.'/components/'.$name.'/'.preg_replace('/^com_/', '', $name);
				break;
			case 'module':
				$path =  JApplicationHelper::getClientInfo($client, true)->path.'/modules/'.$name.'/'.$name;
				break;
			case 'plugin':
				$path = JPATH_PLUGINS.'/'.$group.'/'.$name.'/'.$name;
				break;
			case 'library':
				$path = JPATH_MANIFESTS.'/libraries/'.$name;
				break;
			default:
				throw new InvalidArgumentException(sprintf('Extension type "%s" does not support.', $type));
		}

		return Jpath::clean($reference ? str_replace(JPATH_ROOT, '', $path) : $path);
	}

	protected function getTempPath()
	{
		$path = JPath::clean($this->get('tmp_path'));

		if (!is_dir($path))
		{
			throw new Exception(sprintf('Dir "%s" does not exists.', $path));
		}

		if (!is_writable($path))
		{
			throw new Exception(sprintf('Dir "%s" does not writable.', $path));
		}

		return $path;
	}

	protected static function error($text)
	{
		return sprintf("\033[%sm%s\033[0m", implode(';', self::$error), $text);
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('CreateExtensionCli')->execute();
