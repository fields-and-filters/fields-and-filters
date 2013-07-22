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

// Load the Fieldsandfilters Helper
JLoader::import( 'fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

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
		
		// Trigger the onFinderBeforeSave event.
		$results = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersPrepareForm', array( $form, $data ) );
		
		if( in_array( false, $results, true ) )
		{
			return false;
		}
		
		return true;
	}
	
	public function onContentPrepare( $context, $row, $params, $page = 0 )
	{
		// Don't run this plugin when the content is being indexed
		if( $context == 'com_finder.indexer' )
		{
			return true;
		}
		
		if( $this->params->get( 'prepare_content', 0 ) && ( $interpolation = $this->params->get( 'interpolation', '#{%s}' ) ) && strpos( $interpolation, '%s' ) !== false )
		{
		
			JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
			
			// Trigger the onFinderBeforeSave event.
			FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersContentPrepare', array( $context, $row, $params, $page = 0 ) );
		
		}
	}
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since       1.1.0
	 
	public function onContentPrepare( $context, &$row, &$params, $page = 0 )
	{
		// Don't run this plugin when the content is being indexed
		if( $context == 'com_finder.indexer' )
		{
			return true;
		}
		
		if( $this->params->get( 'prepare_content', 1 ) && ( $interpolation = $this->params->get( 'interpolation', '#{%s}' ) ) && strpos( $interpolation, '%s' ) !== false )
		{
		
			JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
			
			// Trigger the onFinderBeforeSave event.
			FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersContentPrepare', array( $context, &$row, &$params, $page = 0 ) );
		
		}
		if( $this->params->get( 'prepare_content', 1 ) && ( $interpolation = $this->params->get( 'interpolation', '#{%s}' ) ) && strpos( $interpolation, '%s' ) !== false )
		{
			$regex = '/' . sprintf( $interpolation, '(.*?)' ) . '/i';
			$prefix = explode( '%s', $interpolation );
			
			// simple performance check to determine whether bot should process further
			if( !( $prefix = $prefix[0] ) || strpos( $row->text, $prefix ) === false )
			{
				return true;
			}
			
			// Find all instances of plugin and put in $matches for loadposition
			// $matches[0] is full pattern match, $matches[1] is the position
			preg_match_all( $regex, $row->text, $matches, PREG_SET_ORDER );
			
			if( !$matches )
			{
				return true;
			}
			
			$itemID			= isset( $row->id ) ? (int) $row->id: null;
			$matchesID		= array();
			$combinations 		= array();
			$getAllextensions 	= true;
			
			foreach( $matches as $match )
			{
				$matcheslist 	= explode( ',', $match[1] );
				
				if( array_key_exists( 2, $matcheslist ) && ( $_itemID = (int) $matcheslist[2] ) )
				{
					$itemID = $_itemID;
				}
				
				if( !array_key_exists( $itemID, $combinations ) )
				{
					$combinations[$itemID] = array( 'item_id' => $itemID );
					$combinations[$itemID]['option'] = ( array_key_exists( 1, $matcheslist ) && ( $option = trim( $matcheslist[1] ) ) ) ? $option : null;
				}
				
				$fieldID 			= (int) $matcheslist[0];
				$matchesID[$itemID][$fieldID] 	= $match[0];
				
				$combinations[$itemID]['fields_id'][] = $fieldID;
				
			}
			
			while( $combination = array_shift( $combinations ) )
			{
				$itemID = $combination['item_id'];
				$fields = FieldsandfiltersFactory::getFieldsSite()->getFieldsByItemIDWithTemplate( $combination['option'], $itemID, $combination['fields_id'], $getAllextensions, false, 'field_id' );
				$maches	= $matchesID[$itemID];
				
				foreach( $maches AS $id => &$match )
				{
					$field = $fields->get( $id, '' );
					
					$row->text = str_replace( $match, addcslashes( $field, '\\$' ), $row->text );
				}
			}
		}
		
		return true;
		
	}
	*/
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
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
	 * @param	int		The 'page' number
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
	 * @param	int		The 'page' number
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
}
