<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * KextensionsLog.
 *
 * @since       1.0.0
 */
class KextensionsLog
{
	/**
	 * Options array for the JLog instance.
	 *
	 * @var    array
	 * @since       1.0.0
	 */
	protected $options = array();

	/**
	 * @since       1.0.0
	 */
	protected $data = null;

	/**
	 * @var    string  The full filesystem path for the log file.
	 * @since       1.0.0
	 */
	protected $path;

	protected $save = false;

	/**
	 * The global JLog instance.
	 *
	 * @var    JLog
	 * @since       1.0.0
	 */
	protected static $_instances = array();

	/**
	 * Constructor.
	 *
	 * @param   array &$options Log object options.
	 *
	 * @since       1.0.0
	 */
	public function __construct(array &$options)
	{
		// Set the options for the class.
		$this->options = & $options;

		// The name of the text file defaults to 'error.php' if not explicitly given.
		if (empty($this->options['text_file']))
		{
			$this->options['text_file'] = 'kextensions.php';
		}

		// The name of the text file path defaults to that which is set in configuration if not explicitly given.
		if (empty($this->options['text_file_path']))
		{
			$this->options['text_file_path'] = JFactory::getConfig()->get('log_path');
		}

		// False to treat the log file as a php file.
		if (empty($this->options['text_file_no_php']))
		{
			$this->options['text_file_no_php'] = false;
		}

		//
		if (isset($this->options['save']))
		{
			$this->save = (boolean) $this->options['save'];
		}

		// Build the full path to the log file.
		$this->path = $this->options['text_file_path'] . '/' . $this->options['text_file'];

		// get string and creat JRegistry object
		$string     = self::_getString($this->path);
		$this->data = new JRegistry;
		$this->data->loadString($string);
	}

	/**
	 * @since       1.0.0
	 */
	public static function getInstance(array $options)
	{
		$signature = md5(serialize($options));

		// Only create the object if it doesn't exist.
		if (empty(self::$_instances[$signature]))
		{
			self::$_instances[$signature] = new KextensionsLog($options);
		}

		return self::$_instances[$signature];
	}

	/**
	 * @since       1.0.0
	 */
	public static function getEntries($options)
	{
		$instance = self::getInstance($options);

		return $instance->getData();
	}

	/**
	 * @since       1.0.0
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @since       1.0.0
	 */
	public function doSave()
	{
		$this->save = true;
	}

	/**
	 * @since       1.0.0
	 */
	public function isSave()
	{
		return $this->save;
	}

	/**
	 * @since       1.0.0
	 */
	public function save()
	{
		if ($this->data instanceof JRegistry)
		{
			$buffer = $this->_generateFileHeader() . $this->data->toString();

			// Write the new entry to the file.
			if (!JFile::write($this->path, $buffer))
			{
				throw new RuntimeException('Cannot write to log file.');
			}
		}
	}

	/**
	 * Method to generate the log file header.
	 *
	 * @return  string  The log file header
	 *
	 * @since       1.0.0
	 */
	protected function _generateFileHeader()
	{
		$head = array();

		// Build the log file header.

		// If the no php flag is not set add the php die statement.
		if (empty($this->options['text_file_no_php']))
		{
			// Blank line to prevent information disclose: https://bugs.php.net/bug.php?id=60677
			$head[] = '#';
			$head[] = '#<?php die(\'Forbidden.\'); ?>';
		}
		$head[] = '#Date: ' . gmdate('Y-m-d H:i:s') . ' UTC';
		$head[] = '#Software: ' . JPlatform::getLongVersion();
		$head[] = '';

		return implode("\n", $head);
	}

	/**
	 * @since       1.0.0
	 */
	protected static function _getString($path)
	{
		if (!is_file($path) || !($string = file_get_contents($path)) ||
			strpos($string, '{') === false || strrpos($string, '}') === false
		)
		{
			return '{}';
		}

		$len   = strlen($string);
		$start = strpos($string, '{');
		$end   = strrpos($string, '}') + 1;
		$end   = ($end != $len) ? ($end - $len) : $end;

		return substr($string, $start, $end);
	}

	/**
	 * @since       1.0.0
	 */
	public function __destruct()
	{
		while ($instance = array_shift(self::$_instances))
		{
			if ($instance->isSave())
			{
				$instance->save();
			}
		}
	}
}