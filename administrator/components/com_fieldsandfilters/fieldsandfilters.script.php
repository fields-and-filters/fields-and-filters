<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JLoader::import('joomla.filesystem.path');
JLoader::import('joomla.filesystem.folder');

/**
 * Script file of FieldsAndFilters Installer component
 */
class com_fieldsandfiltersInstallerScript
{
	protected $helper;

	protected static $remove_folders = array(
		'/administrator/components/com_fieldsandfilters/helpers',
		'/components/com_fieldsandfilters/helpers'
	);

	
	/**
         * Method to run before an install/update/uninstall method
         * $adapter is the class calling this method
         * $type is the type of change (install, update or discover_install)
         *
         * @return void
         */
        function preflight($type, $adapter) 
        {
		if (!$this->createHelper($type, $adapter))
		{
			return;
		}
		
		$helper = $this->getHelper();
		
		if ($type == 'update' && version_compare($helper->getOldVersion(), 1.2, '<'))
		{
			self::removeFolders();
			
			self::changeLayoutsPlugins('fieldsandfiltersTypes');
			self::changePlugins('fieldsandfiltersExtensions');
			self::changePlugins('fieldsandfiltersTypes');
			
			self::changeModules('mod_fieldsandfilters', 'mod_fieldsandfilters_filters');
		}
	}
	
	/**
         * Method to run after an install/update/uninstall method
         * $adapter is the class calling this method
         * $type is the type of change (install, update or discover_install)
         *
         * @return void
         */
        function postflight($type, $adapter) 
        {
		if (!$this->createHelper($type, $adapter))
		{
			return;
		}
		
		$helper = $this->getHelper();
		
		if ($type == 'install' || ($type == 'update'))
		{
			$helper->checkContentTypes('com_fieldsandfilters.allextensions');
		}
		
		return true;
        }
	
	protected function createHelper($type, $adapter)
	{
		if (!(self::loadClass('script', $adapter) && self::loadClass('contenttype', $adapter)))
		{
			return false;
		}
		
		if (!$this->helper instanceof FieldsandfiltersInstallerScript)
		{
			$this->helper 	= new FieldsandfiltersInstallerScript($type, $adapter, 'allextensions');
			if ($type = 'uninstall')
			{
				/* content type: com_fieldsandfilters.field */
				$this->helper->getContentType('com_fieldsandfilters.field')
					->set('type_title', 'Fieldsandfilters Field')
					->set('type_alias', 'com_fieldsandfilters.field')
					->set('table.special', array(
						'dbtable' => '#__fieldsandfilters_fields',
						'key'     => 'id',
						'type'    => 'Field',
						'prefix'  => 'FieldsandfiltersTable',
					))
					->set('field_mappings.common', array(
						'core_content_item_id'	=> 'id',
						'core_title'		=> 'name',
						'core_state'		=> 'state',
						'core_alias'		=> 'alias',
						'core_body'		    => 'description',
						'core_access'		=> 'access',
						'core_params'		=> 'params',
						'core_language'		=> 'language',
						'core_ordering'		=> 'ordering'
					))
					->set('field_mappings.special', array(
						'type'		        => 'type',
						'content_type_id'	=> 'content_type_id',
						'mode'			    => 'mode',
						'required'		    => 'required'
					))
					->set('content_history_options.formFile', 'administrator/components/com_fieldsandfilters/models/forms/field.xml')
					->addHistoryOptions('hideFields', 'mode')
					->addHistoryOptions('convertToInt', array('content_type_id', 'mode', 'ordering', 'state', 'required'))
					->addDisplayLookup('content_type_id', '#__content_types', 'type_id', 'type_title');

				/* content type: com_fieldsandfilters.field */
				$this->helper->getContentType('com_fieldsandfilters.fieldvalue')
					->set('type_title', 'Fieldsandfilters Field Value')
					->set('type_alias', 'com_fieldsandfilters.fieldvalue')
					->set('table.special', array(
						'dbtable' => '#__fieldsandfilters_field_values',
						'key'     => 'id',
						'type'    => 'Fieldvalue',
						'prefix'  => 'FieldsandfiltersTable',
					))
					->set('field_mappings.common', array(
						'core_content_item_id'	=> 'id',
						'core_title'		=> 'value',
						'core_state'		=> 'state',
						'core_alias'		=> 'alias',
						'core_body'		    => 'value',
						'core_ordering'		=> 'ordering',
						'core_catid'        => 'field_id'
					))
					->set('content_history_options.formFile', 'administrator/components/com_fieldsandfilters/models/forms/fieldvalue.xml')
					->addHistoryOptions('convertToInt', array('field_id', 'ordering', 'state', 'required'))
					->addDisplayLookup('field_id', '#__fieldsandfilters_fields', 'id', 'name');

				/* content type: com_fieldsandfilters.allextensions */
				$this->helper->getContentType('com_fieldsandfilters.allextensions')
					->set('type_title', 'Fieldsandfilters Allextensions')
					->set('type_alias', 'com_fieldsandfilters.allextensions')
					->set('table.special', array(
						'dbtable' => '#__content_types',
						'key'     => 'type_id',
						'type'    => 'JTable',
						'prefix'  => 'Contenttype',
					))
					->set('table.common', new stdClass)
					->set('field_mappings.common', array(
						'core_content_item_id'	=> 'type_id',
						'core_title'		=> 'type_title',
						'core_alias'		=> 'type_alias',
					))
					->set('field_mappings.special', array(
						'table'				=> 'table',
						'rules'				=> 'rules',
						'field_mappings'		=> 'field_mappings',
						'router'			=> 'router',
						'content_history_options' 	=> 'content_history_options'
					))
					->set('content_history_options.formFile', 'administrator/components/com_fieldsandfilters/models/forms/allextensions/extension.xml');
			}
		}
		else
		{
			$this->helper->setType($type);
		}
		
		return true;
	}
	
	protected function getHelper()
	{
		return $this->helper;
	}
	
	protected static function loadClass($class, $adapter)
	{
		$installerClass = 'FieldsandfiltersInstaller' . ucfirst($class);
		if (!class_exists($installerClass))
		{
			$path = 'administrator.helpers.installer.' . strtolower($class);
			JLoader::import($path, $adapter->getParent()->getPath('source'));
			
			if (!class_exists($installerClass))
			{
				// FieldsandfiltersInstallerScript error
				JFactory::getApplication()->enqueueMessage($installerClass . ' class not exists', 'error');
				return false;
			}
		}
		
		return true;
	}

	// [TODO] For test
	protected static function changePluginName($folder, $oldName, $newName)
	{
		$path = JPath::clean(JPATH_PLUGINS.'/'.$folder.'/'.$oldName);
		if (!is_dir($path))
		{
			return;
		}

		$table = JTable::getInstance('extension');

		if (!$table->load(array('type' => 'plugin', 'element' => $oldName, 'folder' => $folder), true))
		{
			return;
		}

		$table->element     = $newName;
		$table->name        = 'plg_' . $table->folder . '_' . $table->element;
		$table->store();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__fieldsandfilters_fields'))
			->set($db->quoteName('type') . ' = ' . $db->quote($table->element))
			->where($db->quoteName('type') . ' = ' . $db->quote($oldName));

		$db->setQuery($query)->execute();

		$langs = JLanguage::getKnownLanguages(JPATH_ADMINISTRATOR);

		// change language name files to lower case
		foreach ($langs AS $lang)
		{
			$pathLang = JPATH_ADMINISTRATOR . '/language/' . $lang['tag'] . '/';
			$filter = '^' . $lang['tag'].'.plg_'.$table->folder.'_'.$oldName.'(.*).ini$';
			$files = JFolder::files($pathLang, $filter);

			foreach ($files AS $file)
			{
				list($langFile, , $ext) = explode('.', $file, 3);
				$newFile = $langFile . '.' . $table->name.'.'.$ext;
				@rename(JPath::clean($pathLang . $file), JPath::clean($pathLang . $newFile));
			}
		}

		// change plugin folder and file names
		$newPath = JPath::clean(JPATH_PLUGINS.'/'.$table->folder.'/'.$table->element);
		@rename($path, $newPath);

		$filter = '^'.$oldName;
		$files = JFolder::files($newPath, $filter);

		foreach ($files AS $file)
		{
			list(,$ext) = explode('.', $file, 2);
			@rename(JPath::clean($newPath.'/'.$file), JPath::clean($newPath.'/'.$table->element.'.'.$ext));
		}

		$templates = self::getTempaltes();

		if (!empty($templates))
		{
			foreach ($templates as $template)
			{
				$path = JPath::clean(JPATH_SITE . '/templates/'.$template->element.'/html/');
				$oldPath = $path = JPath::clean($path.'plg_'.$table->folder.'_'.$oldName);

				if (is_dir($oldPath))
				{
					@rename($oldPath, JPath::clean($path.'/'.$table->name));
				}
			}
		}
	}
	
	protected static function changePlugins($folder)
	{
		$path = JPath::clean(JPATH_PLUGINS . '/' . $folder);
		if (!is_dir($path))
		{
			return;
		}

		$db = JFactory::getDbo();
		
		// change folder and name plugins in table extensions
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where(array(
				$db->quoteName('folder') . ' = ' . $db->quote($folder),
				$db->quoteName('type') . ' = ' . $db->quote('plugin')
			));
		
		$plugins = (array) $db->setQuery($query)->loadObjectList();
		
		while ($plugin = array_shift($plugins))
		{
			$plugin->folder = strtolower($plugin->folder);
			$plugin->name = 'plg_' . $plugin->folder . '_' . $plugin->element;
			
			$db->updateObject('#__extensions', $plugin, 'extension_id');
		}
		
		$langs = JLanguage::getKnownLanguages(JPATH_ADMINISTRATOR);
		
		// change language name files to lower case
		foreach ($langs AS $lang)
		{
			$pathLang = JPATH_ADMINISTRATOR . '/language/' . $lang['tag'] . '/';
			$filter = '^' . $lang['tag'] . '.plg_' . $folder . '_(.*).ini$'; 
			$files = JFolder::files($pathLang, $filter);
			
			foreach ($files AS $file)
			{
				list($langFile, $newFile) = explode('.', $file, 2);
				$newFile = $langFile . '.' . strtolower($newFile);
				@rename(JPath::clean($pathLang . $file), JPath::clean($pathLang . $newFile));
			}
		}
		
		// change plugin folder name to lower case
		$newPath = JPath::clean(JPATH_PLUGINS . '/' . strtolower($folder));
		@rename($path, $newPath);
		
		// change plugin folder ane to lowe case in templates
		$templates = self::getTempaltes();
		
		if (!empty($templates))
		{
			foreach ($templates as $template)
			{
				$path = JPath::clean(JPATH_SITE . '/templates/' . $template->element . '/html/');
				$filter = '^plg_' . $folder . '_(.*)$';
				
				if (is_dir($path) && ($folders = JFolder::folders($path, $filter)))
				{
					foreach ($folders as $folder)
					{
						$newFolder = strtolower($folder);
						@rename($path . $folder, $path . $newFolder);
					}
				}
			}
		}
	}
	
	protected static function changeLayoutsPlugins($folder)
	{
		$path = JPath::clean(JPATH_PLUGINS . '/' . $folder);
		if (!is_dir($path))
		{
			return;
		}

		$plugins = JFolder::folders($path);
		
		if (empty($plugins))
		{
			return;
		}
		
		$templates = self::getTempaltes();
		
		foreach ($plugins AS $plugin)
		{
			self::moveLayoutsPlugin(JPATH_PLUGINS . "/$folder/$plugin/tmpl");
			
			if (empty($templates))
			{
				continue;
			}
			
			foreach ($templates as $template)
			{
				self::moveLayoutsPlugin(JPATH_SITE . "/templates/{$template->element}/html/plg_{$folder}_{$plugin}");
			}
		}
	}
	
	protected static function moveLayoutsPlugin($path, $filter = '^(filter|field|static)-(.*)\.php$')
	{
		$path = JPath::clean($path);
		if (is_dir($path) && ($files = JFolder::files($path, $filter)) && !empty($files))
		{
			foreach ($files AS $file)
			{
				list($folder, $name) = explode('-', $file, 2);
				
				$pathFolder = JPath::clean($path . "/$folder");
				
				if (!(is_dir($pathFolder) || JFolder::create($pathFolder)))
				{
					continue;
				}
				
				JFile::move($path . "/$file", $pathFolder . "/$name");
			}
		}
	}
	
	protected static function changeModules($old_module, $new_module)
	{
		$db = JFactory::getDbo();
		
		// change name modules in table extensions
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where(array(
				$db->quoteName('type') . ' = ' . $db->quote('module'),
				$db->quoteName('element') . ' = ' . $db->quote($old_module)
			));
		
		$modules = (array) $db->setQuery($query)->loadObjectList();
		
		while ($module = array_shift($modules))
		{
			$module->name = $new_module;
			$module->element = $new_module;
			
			$db->updateObject('#__extensions', $module, 'extension_id');
		}
		
		$query->clear();
		
		// change name modules in table modules
		$query->update($db->quoteName('#__modules'))
			->set($db->quoteName('module') . ' = ' . $db->quote($new_module))
			->where($db->quotename('module') . ' = ' . $db->quote($old_module));
			
		$db->setQuery($query)->execute();
		
		$langs = JLanguage::getKnownLanguages(JPATH_SITE);
		
		// change language name files to lower case
		foreach ($langs AS $lang)
		{
			$path = JPATH_SITE . '/language/' . $lang['tag'] . '/';
			$filter = '^' . $lang['tag'] . '\.' . $old_module . '\.(sys.ini|ini)$'; 
			$files = JFolder::files($path, $filter);
			
			foreach ($files AS $file)
			{
				$newFile = str_replace($old_module, $new_module, $file);
				@rename(JPath::clean($path . $file), JPath::clean($path . $newFile));
			}
		}
		
		// change plugin folder name to lower case
		$path = JPath::clean(JPATH_SITE . '/modules/' . $old_module . '/');
		
		if (is_dir($path))
		{
			$filter = '^' . $old_module . '\.(php|xml)$'; 
			$files = JFolder::files($path, $filter);
			
			foreach ($files AS $file)
			{
				$newFile = str_replace($old_module, $new_module, $file);
				@rename($path . $file, $path . $newFile);
			}
			
			$newPath = JPath::clean(JPATH_SITE . '/modules/' . $new_module);
			@rename($path, $newPath);
		}
		
		// change plugin folder ane to lowe case in templates
		$templates = self::getTempaltes();
		
		if (!empty($templates))
		{
			foreach ($templates as $template)
			{
				$path = JPath::clean(JPATH_SITE . '/templates/' . $template->element . '/html/' . $old_module);
				
				// change plugin folder name to lower case
				if (is_dir($path))
				{
					$newPath = JPath::clean(JPATH_SITE . '/templates/' . $template->element . '/html/' . $new_module);
					@rename($path, $newPath);
				}
			}
		}
		
	}
	
	protected static function getTempaltes()
	{
		static $templates;
		
		if (is_null($templates))
		{
			$db = JFactory::getDbo();
			// Build the query.
			$query = $db->getQuery(true);
			$query->select(array(
				       $db->quoteName('element'),
				       $db->quoteName('name')
				))
				->from($db->quoteName('#__extensions', 'e'))
				->where($db->quoteName('e.client_id') . ' = ' . 0)
				->where($db->quoteName('e.type') . ' = ' . $db->quote('template'))
				->where($db->quoteName('e.enabled') . ' = 1');
			
			$templates = (array) $db->setQuery($query)->loadObjectList('element');
		}
		
		return $templates;
	}
	
	public static function removeFolders()
	{
		if (!empty(self::$remove_folders))
		{
			while ($folder = array_shift(self::$remove_folders))
			{
				$path = JPATH_ROOT . $folder;
				if (is_dir($path))
				{
					JFolder::delete($path);
				}
			}
		}
	}
}