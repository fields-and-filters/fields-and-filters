<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * FieldsandfiltersInstallerScript
 *
 * @package     com_fieldsandfilters
 * @since       1.2.0
 */
class FieldsandfiltersInstallerScript
{
	protected $adapter;
	protected $type;
	protected $contentType = array();
	protected $oldExtension;

	public function __construct($type, JAdapterInstance $adapter, $oldExtensionType = null)
	{
		$this->type             = $type;
		$this->adapter          = $adapter;
		$this->oldExtensionType = $oldExtensionType;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getAdapter()
	{
		return $this->adapter;
	}

	public function getContentType($name)
	{
		if (!isset($this->contentType[$name]) || !$this->contentType[$name] instanceof FieldsandfiltersInstallerContenttype)
		{
			$this->contentType[$name] = new FieldsandfiltersInstallerContenttype;
		}

		return $this->contentType[$name];
	}

	public function getVersion()
	{
		static $version;

		if (is_null($version))
		{
			$version = (float) $this->getAdapter()->getParent()->manifest['version'];
		}

		return $version;
	}

	public function getOldVersion()
	{
		static $oldVersion;

		if (is_null($oldVersion))
		{
			// load problem
			$table = JTable::getInstance('extension');

			$version = $this->getVersion();
			if ($table->load(array('element' => $this->getAdapter()->get('element'), 'type' => 'component')))
			{
				$manifestCache = new JRegistry($table->manifest_cache);
				$version       = ($manifestCache->get('version')) ? (float) $manifestCache->get('version') : $version;
			}

			$oldVersion = $version;
		}

		return $oldVersion;
	}

	public function checkContentTypes($type)
	{
		foreach ($this->contentType AS &$contentType)
		{
			self::checkContentType($contentType);
		}

		if ($this->type == 'update' && version_compare($this->getOldVersion(), 1.2, '<'))
		{
			self::updateContentType($type);
		}
	}

	protected static function checkContentType(FieldsandfiltersInstallerContenttype $contentType)
	{
		if (!($contentTypeAlias = $contentType->get('type_alias')))
		{
			return;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('type_alias'))
			->from($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . '=' . $db->quote($contentTypeAlias));

		if (!$db->setQuery($query)->loadResult())
		{
			$columns = array_keys($db->getTableColumns('#__content_types'));

			if (!empty($columns))
			{
				$contentType = (array) $contentType->toContentType($columns);

				if (!empty($contentType))
				{
					$query->clear()
						->insert($db->quoteName('#__content_types'))
						->columns($db->quoteName(array_keys($contentType)))
						// ->values(implode(', ', $db->quote(array_values($contentType), false))) // Joomla! 3.x
						->values(implode(', ', array_map(array($db, 'quote'), array_values($contentType), array_fill(0, count($contentType), false))));

					$db->setQuery($query)->execute();
				}
			}
		}
	}

	protected function updateContentType($type)
	{
		$contentType = $this->getContentType($type);

		if (!($contentTypeAlias = $contentType->get('type_alias')) || $this->oldExtensionType)
		{
			return;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('type_id'))
			->from($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote($contentTypeAlias));

		$contentTypeID = (int) $db->setQuery($query)->loadResult();

		if (!$contentTypeID)
		{
			return;
		}

		// get Extension Type ID
		$query->clear()
			->select($db->quoteName('extension_type_id'))
			->from($db->quoteName('#__fieldsandfilters_extensions_type'))
			->where($db->quoteName('extension_name') . ' = ' . $db->quote($this->oldExtensionType));

		$extensionTypeID = (int) $db->setQuery($query)->loadResult();

		if (!$extensionTypeID || $contentTypeID == $extensionTypeID)
		{
			return;
		}

		// update old extension type id
		$tables = array(
			'#__fieldsandfilters_connections',
			'#__fieldsandfilters_data',
			'#__fieldsandfilters_elements',
			'#__fieldsandfilters_fields'
		);

		$query->clear()
			->set('content_type_id = ' . (int) $contentTypeID)
			->where('content_type_id = ' . (int) $extensionTypeID);

		while ($table = array_shift($tables))
		{

			$query->clear('update')
				->update($db->quoteName($table));

			$db->setQuery($query)->execute();
		}
	}
}