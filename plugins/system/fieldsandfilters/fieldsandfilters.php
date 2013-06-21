<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.checkbox
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.utilities.utility');

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
	
	protected $_dispatcher;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.0.0
	 */
	public function __construct( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		
		if( version_compare( JVERSION, 3.0, '<' ) )
		{
			$this->_dispatcher = JDispatcher::getInstance();
		}
		else
		{
			$this->_dispatcher = JEventDispatcher::getInstance();
		}
	}
	
	/**
	 * Fieldsandfilters before save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since   2.5
	 */
	public function onContentBeforeSave( $context, $article, $isNew )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFinderBeforeSave event.
		$results = $this->_dispatcher->trigger( 'onFieldsandfiltersBeforeSave', array( $context, $article, $isNew ) );
		
		if( in_array( false, $results, true ) )
		{
			return false;
		}
		
		return true;

	}
	
	public function onContentAfterSave($context, $item, $isNew)
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFinderBeforeSave event.
		$this->_dispatcher->trigger( 'onFieldsandfiltersAfterSave', array( $context, $item, $isNew ) );
		
		return true;

	}
	
	public function onContentBeforeDelete( $context, $table )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFinderBeforeSave event.
		$results = $this->_dispatcher->trigger( 'onFieldsandfiltersBeforeDelete', array( $context, $table ) );
		
		if( in_array( false, $results, true ) )
		{
			return false;
		}
		
		return true;
	}
	
	public function onContentChangeState( $context, $pks, $value )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFinderBeforeSave event.
		$results = $this->_dispatcher->trigger( 'onFieldsandfiltersChangeState', array( $context, $pks, $value ) );
		
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
	 * @since	2.5
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
		
		// Trigger the onFinderBeforeSave event.
		$results = $this->_dispatcher->trigger( 'onFieldsandfiltersPrepareForm', array( $form, $data ) );
		
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
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onContentPrepare( $context, &$row, &$params, $page = 0 )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFinderBeforeSave event.
		$this->_dispatcher->trigger( 'onFieldsandfiltersContentPrepare', array( $context, &$row, &$params, $page = 0 ) );
		
		return true;
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onContentAfterTitle( $context, &$row, &$params, $page = 0 )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFieldsandfiltersContentAfterTitle event.
		$results = $this->_dispatcher->trigger( 'onFieldsandfiltersContentAfterTitle', array( $context, &$row, &$params, $page = 0 ) );
		
		return ( !empty( $results ) ? trim( implode( "\n", $results ) ) : null );
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onContentBeforeDisplay( $context, &$row, &$params, $page = 0 )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFinderBeforeSave event.
		$results = $this->_dispatcher->trigger( 'onFieldsandfiltersContentBeforeDisplay', array( $context, &$row, &$params, $page = 0 ) );
		
		return ( !empty( $results ) ? trim( implode( "\n", $results ) ) : null );
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onContentAfterDisplay( $context, &$row, &$params, $page = 0 )
	{
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onFinderBeforeSave event.
		$results = $this->_dispatcher->trigger( 'onFieldsandfiltersContentAfterDisplay', array( $context, &$row, &$params, $page = 0 ) );
		
		return ( !empty( $results ) ? trim( implode( "\n", $results ) ) : null );
	}
}
