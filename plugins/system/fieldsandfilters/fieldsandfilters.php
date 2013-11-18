<?php
/**
 * @version     1.1.1
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.checkbox
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
 * Checkbox type fild
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_types.checkbox
 * @since       1.0.0
 */
class plgSystemFieldsandfilters extends JPlugin
{
	/**
	 * @var		string	Folder plugin extensions types name.
	 * @since	1.0.0
	 */
	protected $_folder_plugin_extensions = 'fieldsandfiltersExtensions';

	/**
         * @since       1.2.0
         */
	public function onAfterInitialise()
	{
		JLoader::registerPrefix( 'Kextensions', JPATH_LIBRARIES . '/kextensions' );
		JLoader::registerPrefix( 'Fieldsandfilters', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );
	}
	
	/**
	 * Fieldsandfilters before save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since       1.1.0
	 */
	public function onContentBeforeSave( $context, $article, $isNew )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFinderBeforeSave event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersBeforeSave', array( $context, $article, $isNew ) );
		
		if( in_array( false, $results, true ) )
		{
			return false;
		}
		
		return true;

	}
	
	/**
	 * @since       1.1.0
	 */
	public function onContentAfterSave($context, $item, $isNew)
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFinderBeforeSave event.
		FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersAfterSave', array( $context, $item, $isNew ) );
		
		return true;

	}
	
	/**
	 * @since       1.1.0
	 */
	public function onContentBeforeDelete( $context, $table )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFinderBeforeSave event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersBeforeDelete', array( $context, $table ) );
		
		if( in_array( false, $results, true ) )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onContentChangeState( $context, $pks, $value )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFinderBeforeSave event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersChangeState', array( $context, $pks, $value ) );
		
		if( in_array( false, $results, true ) )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since       1.1.0
	 */
	public function onContentPrepareForm( $form, $data )
	{
		// Check we have a form
		if( !( $form instanceof JForm ) )
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFinderBeforeSave event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersPrepareForm', array( $form, $data ) );
		
		if( in_array( false, $results, true ) )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int	The 'page' number
	 *
	 * @return	void
	 * @since       1.1.0
	 */
	public function onContentPrepare( $context, $row, $params, $page = 0 )
	{
		// Don't run this plugin when the content is being indexed
		if( $context == 'com_finder.indexer' || !$this->params->get( 'prepare_content', 1 ) || !( $syntax = $this->params->get( 'syntax', '#{%s}' ) ) || !property_exists( $row, 'text' ) )
		{
			return true;
		}
		
		FieldsandfiltersFieldsHelper::preparationContent( $row->text, $context, null, ( property_exists( $row, 'id' ) ? $row->id : null ), array(), $syntax, $this->params->get( 'syntax_type', FieldsandfiltersFieldsHelper::SYNTAX_SIMPLE ) );
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int	The 'page' number
	 *
	 * @return	void
	 * @since       1.1.0
	 */
	public function onContentAfterTitle( $context, &$row, &$params, $page = 0 )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFieldsandfiltersContentAfterTitle event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersContentAfterTitle', array( $context, &$row, &$params, $page = 0 ) );
		
		return ( !empty( $results ) ? trim( implode( "\n", $results ) ) : null );
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int	The 'page' number
	 *
	 * @return	void
	 * @since       1.1.0
	 */
	public function onContentBeforeDisplay( $context, &$row, &$params, $page = 0 )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFinderBeforeSave event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersContentBeforeDisplay', array( $context, &$row, &$params, $page = 0 ) );
		
		return ( !empty( $results ) ? trim( implode( "\n", $results ) ) : null );
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int	The 'page' number
	 *
	 * @return	void
	 * @since       1.1.0
	 */
	public function onContentAfterDisplay( $context, &$row, &$params, $page = 0 )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFinderBeforeSave event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersContentAfterDisplay', array( $context, &$row, &$params, $page = 0 ) );
		
		return ( !empty( $results ) ? trim( implode( "\n", $results ) ) : null );
	}
	
	/**
	 * @since       1.2.0
	 */
	public function onAfterRender()
	{
		if( JFactory::getApplication()->isAdmin() || !$this->params->get( 'prepare_after_render', 0 ) && !( $syntax = $this->params->get( 'syntax', '#{%s}' ) ) )
		{
			return;
		}
		
		$buffer = JResponse::getBody();
		
		FieldsandfiltersFieldsHelper::preparationContent( $buffer, 'system', null, null, array(), $syntax, $this->params->get( 'syntax_type', FieldsandfiltersFieldsHelper::SYNTAX_SIMPLE ) );
	
		JResponse::setBody( $buffer );
	}
}
