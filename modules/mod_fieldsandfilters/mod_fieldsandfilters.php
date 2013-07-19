<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @subpackage  mod_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

// Load the BufferCore Helper
JLoader::import( 'fieldsandfilters.buffer.core', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

if( $fieldsID = $params->get( 'fields_id' ) )
{
	$jinput = JFactory::getApplication()->input;
	$context = $jinput->get( 'option' ) . '.' . $jinput->get( 'view' );
	
	JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
	
	// Trigger the onFieldsandfiltersPrepareFiltersHTML event.
	$templateFilters = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersPrepareFiltersHTML', array( $context, $fieldsID, $params->get( 'getAllextensions', true ), false ) );
	$templateFilters = implode( "\n", $templateFilters );
	
	$jregistry 	= JRegistry::getInstance( 'fieldsandfilters' );
	$filtersRequest = $jregistry->get( 'filters.request', new stdClass );
	$counts 	= $jregistry->get( 'filters.counts', array() );
	$pagination 	= (array) $jregistry->get( 'filters.pagination', array() );
	
	
	if( property_exists( $filtersRequest, 'extensionID' ) && !empty( $counts ) )
	{
		// Import JS
		JHtml::_( 'jquery.framework' );
		JHtml::_( 'script', 'fieldsandfilters/component/jquery.fieldsandfilters.js', false, true );
			
		$request = array(
				'option' 	=> 'com_fieldsandfilters',
				'task' 		=> 'request.filters',
				'tmpl' 		=> 'component',
				'format'	=> 'json',
				'Itemid'	=> $jinput->get( 'Itemid', 0, 'int' )
		);
		
		$request = array_merge( $request, (array) get_object_vars( $filtersRequest ) );
		
		$options = array(
			'url' 		=> JRoute::_( JURI::root(true) . '/index.php' ),
			'token'		=> JSession::getFormToken(),
			'fields'	=> $fieldsID,
			'module' 	=> $module->id,
			'counts'	=> $jregistry->get( 'filters.counts', array() ),
			'request'	=> $request,
			'pagination'	=> ( !empty( $pagination ) ? $pagination : array( 'limitstart' => 0 ) )
		);
		
		// get component params
		$fieldsandfilters = JComponentHelper::getParams( 'com_fieldsandfilters' );
		
		// get selectors
		if( $selectorBody = trim( $fieldsandfilters->get( 'selector_body' ) ) )
		{
			$selectors['body'] = $selectorBody;
		}
		
		if( !empty( $selectors ) && is_array( $selectors ) )
		{
			$options['selectors'] = $selectors;
		}
		
		// get functions
		if( $functionDone = trim( $fieldsandfilters->get( 'function_done' ) ) )
		{
			$fn['done'] = '\\' . $functionDone;
		}
		
		if( !empty( $fn ) && is_array( $fn ) )
		{
			$options['fn'] = $fn;
		}
		
		$script[]       = 'jQuery(document).ready(function($) {';
		$script[]       = '     $( "#faf-form-' . $module->id . '" ).fieldsandfilters(' .  JHtml::getJSObject( $options ) . ');';
		$script[]       = '});';
		
		JFactory::getDocument()->addScriptDeclaration( implode( "\n", $script ) );
		
		
		require JModuleHelper::getLayoutPath( 'mod_fieldsandfilters', $params->get( 'layout', 'default' ) );
	}
}