<?php
/**
 * @version     1.1.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extension.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
* @since       1.0.0
*/
class plgFieldsandfiltersExtensionsContentInstallerScript
{
	protected $helper;
	
	/**
         * Method to run after an install/update/uninstall method
         * $adapter is the class calling this method
         * $type is the type of change (install, update or discover_install)
         *
         * @return void
         */
        function postflight( $type, $adapter ) 
        {
		if( !$this->createHelper( $type, $adapter ) )
		{
			return;
		}
		
		$helper = $this->getHelper( $type, $adapter );
		
		if( $type == 'install' || ( $type == 'update' && version_compare( $helper->getOldVersion(), 1.2, '<' ) ) )
		{
			$helper->checkContentTypes('com_content.article');
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
				$this->helper->getContentType('com_content.article')
					->set('type_title', 'Article')
					->set('type_alias', 'com_content.article')
					->set('table.special', array(
						'dbtable' => '#__content',
						'key'     => 'id',
						'type'    => 'Content',
						'prefix'  => 'JTable',
					))
					->set('table.common', array(
						'dbtable' => '#__ucm_content',
						'key'     => 'ucm_id',
						'type'    => 'Corecontent',
						'prefix'  => 'JTable',
					))
					->set('field_mappings.common', array(
						'core_content_item_id' => 'id',
			            'core_title' => 'title',
			            'core_state' => 'state',
			            'core_alias' => 'alias',
			            'core_created_time' => 'created',
			            'core_modified_time' => 'modified',
			            'core_body' => 'introtext',
			            'core_hits' => 'hits',
			            'core_publish_up' => 'publish_up',
			            'core_publish_down' => 'publish_down',
			            'core_access' => 'access',
			            'core_params' => 'attribs',
			            'core_featured' => 'featured',
			            'core_metadata' => 'metadata',
			            'core_language' => 'language',
			            'core_images' => 'images',
			            'core_urls' => 'urls',
			            'core_version' => 'version',
			            'core_ordering' => 'ordering',
			            'core_metakey' => 'metakey',
			            'core_metadesc' => 'metadesc',
			            'core_catid' => 'catid',
			            'core_xreference' => 'xreference',
			            'asset_id' => 'asset_id'
					))
					->set('field_mappings.special', array(
						'fulltext'		        => 'fulltext'
					))
					->set('router', 'ContentHelperRoute::getArticleRoute')
					->set('content_history_options.formFile', 'administrator/components/com_content/models/forms/article.xml')
					->addHistoryOptions('hideFields', array('asset_id', 'checked_out', 'checked_out_time', 'version'))
					->addHistoryOptions('ignoreChanges', array('modified_by', 'modified', 'checked_out', 'checked_out_time', 'version', 'hits'))
					->addHistoryOptions('convertToInt', array('publish_up', 'publish_down', 'featured', 'ordering'))

					->addDisplayLookup('catid', '#__categories', 'id', 'title')
					->addDisplayLookup('created_by', '#__users', 'id', 'name')
					->addDisplayLookup('access', '#__viewlevels', 'id', 'title')
					->addDisplayLookup('modified_by', '#__users', 'id', 'name');
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
}