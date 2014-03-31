<?php
/**
 * @package     com_fieldsandfilters
 * @subpackage  mod_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

if( $fieldsID = $params->get( 'fields_id' ) )
{
	$app = JFactory::getApplication();
	$context = $app->input->get( 'option' ) . '.' . $app->input->get( 'view' );

	// Load Extensions Helper
	$extensionsHelper = FieldsandfiltersFactory::getExtensions();

	// [TODO] change JObject to array
	$extensionsParams = new JObject( array(
					'module.value'		=> $params->get( 'use_allextensions_filters' ),
					'plugin.name'		=> 'content'
			) );
	
	$showAllextensions = FieldsandfiltersExtensionsHelper::getParams( 'use_allextensions_filters', $extensionsParams, true );

	$extensionsParams->set( 'module.value', $params->get( 'selector_body_filters' ) );

	$filters = new JObject();
	$filters->set('selectors', array(
		'body' => trim( FieldsandfiltersExtensionsHelper::getParams( 'selector_body_filters', $extensionsParams, '#content' ) )
	));

	JPluginHelper::importPlugin( 'fieldsandfiltersextensions' );

	// Trigger the onFieldsandfiltersPrepareFiltersHTML event.
	$app->triggerEvent( 'onFieldsandfiltersPrepareFiltersHTML', array( $context, $filters, $fieldsID, $showAllextensions ) );
	
	$filtersRequest = (array) $filters->get( 'request' );
	$counts 	= (array) $filters->get( 'counts' );
	$pagination 	= (array) $filters->get( 'pagination', array( 'limitstart' => 0 ) );

	if( isset( $filtersRequest['extensionID'] ) && $filtersRequest['extensionID'] && !empty( $counts ) )
	{
		$request = array(
				'option' 	=> 'com_fieldsandfilters',
				'task' 		=> 'request.filters',
				'tmpl' 		=> 'component',
				'format'	=> 'json',
				'Itemid'	=> $app->input->get( 'Itemid', 0, 'int' ),
				'context'   => $context
		);
		
		$request = array_merge( $request, $filtersRequest );
		
		$options = array(
			'url' 		=> JRoute::_( JURI::root(true) . '/index.php' ),
			'token'		=> JSession::getFormToken(),
			'fields'	=> $fieldsID,
			'module' 	=> $module->id,
			'counts'	=> $counts,
			'request'	=> $request,
			'pagination'	=> $pagination
		);
		
		// get selectors
		$selectors = $filters->get('selectors');
		if( !empty( $selectors ) && is_array( $selectors ) )
		{
			if (empty($selectors['body']))
			{
				unset($selectors['body']);
			}

			$options['selectors'] = $selectors;
		}
		
		// get functions
		$extensionsParams->set( 'module.value', $params->get( 'function_done_filters' ) );
		if( $functionDone = trim( FieldsandfiltersExtensionsHelper::getParams( 'function_done_filters', $extensionsParams ) ) )
		{
			$fn['done'] = '\\' . $functionDone;
		}
		
		if( !empty( $fn ) && is_array( $fn ) )
		{
			$options['fn'] = $fn;
		}
		
		// Import JS && CSS
		JHtml::_( 'FieldsandfiltersHtml.filters.framework' );
		
		if( FieldsandfiltersFactory::isVersion() )
		{
			$options = JHtml::getJSObject( $options );
		}
		else
		{	
			$options = JHtml::_( 'FieldsandfiltersHtml.joomla.getJSObject', $options );
		}
		
		$script[]       = 'jQuery(document).ready(function($) {';
		$script[]       = '     $( "#faf-form-' . $module->id . '" ).fieldsandfilters(' .  $options . ');';
		$script[]       = '});';
		
		JFactory::getDocument()->addScriptDeclaration( implode( "\n", $script ) );
		
		require JModuleHelper::getLayoutPath( 'mod_fieldsandfilters_filters', $params->get( 'layout', 'default' ) );
	}
}