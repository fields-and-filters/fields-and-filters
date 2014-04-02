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
class FieldsandfiltersInstallerContenttype
{
	protected $data = array(
		'type_title'              => '',
		'type_alias'              => '',
		'table'                   => array(
			'special' => array(
				'dbtable' => '',
				'key'     => '',
				'type'    => '',
				'prefix'  => '',
				'config'  => 'array()',
			),
			'common'  => array(
				'dbtable' => '', // '#__ucm_content'
				'key'     => '', // 'ucm_id'
				'type'    => '', // 'Corecontent'
				'prefix'  => '', // JTable
				'config'  => 'array()',
			)),
		'rules'                   => '',
		'field_mappings'          => array(
			'common'  => array(
				'core_content_item_id' => 'null',
				'core_title'           => 'null',
				'core_state'           => 'null',
				'core_alias'           => 'null',
				'core_created_time'    => 'null',
				'core_modified_time'   => 'null',
				'core_body'            => 'null',
				'core_hits'            => 'null',
				'core_publish_up'      => 'null',
				'core_publish_down'    => 'null',
				'core_access'          => 'null',
				'core_params'          => 'null',
				'core_featured'        => 'null',
				'core_metadata'        => 'null',
				'core_language'        => 'null',
				'core_images'          => 'null',
				'core_urls'            => 'null',
				'core_version'         => 'null',
				'core_ordering'        => 'null',
				'core_metakey'         => 'null',
				'core_metadesc'        => 'null',
				'core_catid'           => 'null',
				'core_xreference'      => 'null',
				'asset_id'             => 'null',
			),
			'special' => array()
		),
		'router'                  => '',
		'content_history_options' => array(
			'formFile'      => '',
			'hideFields'    => array(),
			'ignoreChanges' => array(),
			'convertToInt'  => array(),
			'displayLookup' => array()
		)
	);

	protected static $arrayType = array('hideFields', 'ignoreChanges', 'convertToInt');

	protected static $jsonType = array('table', 'field_mappings', 'content_history_options');

	public function __construct()
	{
		$this->data = new JRegistry($this->data);
		$this->data->set('field_mappings.special', new stdClass);
	}

	/**
	 * Get a registry value.
	 *
	 * @param   string $path    Registry path (e.g. joomla.content.showauthor)
	 * @param   mixed  $default Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   11.1
	 */
	public function get($path = null, $default = null)
	{
		if (is_null($path))
		{
			return $this->data;
		}

		return $this->data->get($path, $default);
	}

	/**
	 * Set a registry value.
	 *
	 * @param   string $path  Registry Path (e.g. joomla.content.showauthor)
	 * @param   mixed  $value Value of entry
	 *
	 * @return  mixed  The value of the that has been set.
	 *
	 * @since   11.1
	 */
	public function set($path, $values)
	{
		// Get the old value if exists so we can return it
		if ((is_array($values) && JArrayHelper::isAssociative($values)) || (is_object($values) && count((array) $values)))
		{
			foreach ($values AS $key => $value)
			{
				$this->data->set("$path.$key", $value);
			}
		}
		else
		{
			$this->data->set($path, $values);
		}

		return $this;
	}

	public function addDisplayLookup($sourceColumn = '', $targetTable = '', $targetColumn = '', $displayColumn = '')
	{
		$key = 'content_history_options.displayLookup';

		$lookup                = new stdClass;
		$lookup->sourceColumn  = $sourceColumn;
		$lookup->targetTable   = $targetTable;
		$lookup->targetColumn  = $targetColumn;
		$lookup->displayColumn = $displayColumn;

		$displayLookup = (array) $this->data->get($key);

		array_push($displayLookup, $lookup);

		$this->data->set($key, $displayLookup);

		return $this;
	}

	public function addHistoryOptions($type, $options = array())
	{
		if (in_array($type, self::$arrayType) && !empty($options))
		{
			$key            = 'content_history_options.' . $type;
			$options        = is_array($options) ? $options : (array) $options;
			$optionsHistroy = $this->get($key);
			$this->set($key, array_merge($optionsHistroy, $options));
		}

		return $this;
	}

	public function toContentType($properties = null)
	{
		if (is_null($properties))
		{
			$contentType = $this->data->toObject();
		}
		else
		{
			$contentType = new stdClass;
			$properties  = is_array($properties) ? $properties : (array) $properties;

			foreach ($properties AS $property)
			{
				if ($this->data->exists($property))
				{
					$contentType->$property = $this->data->get($property);
				}
			}
		}

		foreach (self::$jsonType AS $type)
		{
			if (isset($contentType->$type))
			{
				$contentType->$type = json_encode($contentType->$type);
			}
		}

		return $contentType;
	}
}