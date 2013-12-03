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
			$helper->checkContentType( 'content', 'com_content.article' );
		}
		
		return true;
        }
	
	protected function createHelper( $type, $adapter )
	{
		if( !class_exists( 'FieldsandfiltersInstallerScript' ) )
		{
			JLoader::import( 'com_fieldsandfilters.helpers.installer.script', JPATH_ADMINISTRATOR . '/components' );
			
			if( !class_exists( 'FieldsandfiltersInstallerScript' ) )
			{
				// FieldsandfiltersInstallerScript error
				JFactory::getApplication()->enqueueMessage( 'FieldsandfiltersInstallerScript class not exists', 'error' );
				
				return false;
			}
		}
		
		if( !$this->helper instanceof FieldsandfiltersInstallerScript )
		{
			$this->helper = new FieldsandfiltersInstallerScript( $type, $adapter );
			$this->helper->setContentType( self::prepareContentType() );
		}
		else
		{
			$this->helper->setType( $type );
		}
		
		return true;
	}
	
	protected function getHelper()
	{
		return $this->helper;
	}
	
	protected static function prepareContentType()
	{
		$contentType = new stdClass();
		$contentType->type_title = 'Fieldsandfilters Field';
		$contentType->type_alias = 'com_fieldsandfilters.field';
		$contentType->table = json_encode(
			array(
				'special' => array(
					'dbtable' => '#__fieldsandfilters_fields',
					'key'     => 'field_id',
					'type'    => 'Field',
					'prefix'  => 'FieldsandfiltersTable',
					'config'  => 'array()'
				),
				'common' => array()
			)
		);
		
		$contentType->rules = '';
		
		$contentType->field_mappings = json_encode(
			array(
				'common' => array(
					'core_content_item_id'	=> 'field_id',
					'core_title'		=> 'field_name',
					'core_state'		=> 'state',
					'core_alias'		=> 'field_alias',
					'core_created_time'	=> 'null', // null
					'core_modified_time'	=> 'null', // null
					'core_body'		=> 'description',
					'core_hits'		=> 'null', // null
					'core_publish_up'	=> 'null', // null
					'core_publish_down'	=> 'null', // null
					'core_access'		=> 'access',
					'core_params'		=> 'params',
					'core_featured'		=> 'null', // null
					'core_metadata'		=> 'null', // null
					'core_language'		=> 'language',
					'core_images'		=> 'null', // null
					'core_urls'		=> 'null', // null
					'core_version'		=> 'null', // null
					'core_ordering'		=> 'ordering',
					'core_metakey'		=> 'null', // null
					'core_metadesc'		=> 'null', // null
					'core_catid'		=> 'null', // null
					'core_xreference'	=> 'null', // null
					'asset_id'		=> 'null' // null
				),
				'special' => array(
					'field_type'		=> 'field_type',
					'content_type_id'	=> 'content_type_id',
					'mode'			=> 'mode',
					'required'		=> 'required'
				)
			)
		);
		
		$contentType->router = '';
		
		$contentType->content_history_options = json_encode(
			array(
				'formFile' 		=> 'administrator/components/com_fieldsandfilters/models/forms/field.xml',
				'hideFields' 		=> array( 'mode' ),
				'ignoreChanges' 	=> array(),
				'convertToInt'		=> array( 'content_type_id', 'mode', 'ordering', 'state', 'required' ),
				'displayLookup'		=> array(
					array(
						'sourceColumn'		=> 'content_type_id',
						'targetTable'		=> '#__content_types',
						'targetColumn'		=> 'type_id',
						'displayColumn'		=> 'type_title'
					)
				)
				
				
				
			)
		);
		
		return (array) $contentType;
	}
}