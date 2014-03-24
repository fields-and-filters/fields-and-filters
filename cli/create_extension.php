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
			$this->out($this->error($e->getMessage()));
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

		$archive = JArchive::getAdapter($adapter);

		$files = array(
			array(
				'name' => 'cli/garbagecron.php',
				'data' => file_get_contents(JPATH_ROOT.'/cli/garbagecron.php')
			)
		);

		if (!$archive->create($path, $files))
		{
			throw new Exception(sprintf('File "%s" does not crated.', $path));
		}
	}

	protected function error($text)
	{
		return sprintf("\033[%sm%s\033[0m", implode(';', self::$error), $text);
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('CreateExtensionCli')->execute();
