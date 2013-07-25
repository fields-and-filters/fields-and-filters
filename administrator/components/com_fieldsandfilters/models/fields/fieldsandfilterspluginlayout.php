<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldPluginsType for a select list of type plugins.
 * @since       1.1.0
 */
class JFormFieldFieldsandfiltersPluginLayout extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'FieldsandfiltersPluginLayout';

	/**
	 * Method to get the field input for module layouts.
	 *
	 * @return  string  The field input.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Get the variables
		$pluginKey = ( $v = (string) $this->element['key'] ) ? $v : 'field_type';
		
		// Get the plugin.
		$pluginType = ( $v = (string) $this->element['plugin'] ) ? $v : 'fieldsandfiltersTypes';
		$pluginType = preg_replace('#\W#', '', $pluginType);
		
		// Get the template.
		$modeName = ( $v = (string) $this->element['mode'] ) ? $v : 'field';
		$modeName = preg_replace( '#\W#', '', $modeName );
		
		if( $this->form instanceof JForm)
		{
			$pluginName = $this->form->getValue($pluginKey);
		}
		
		$pluginName = preg_replace('#\W#', '', $pluginName);
		
		if( $pluginName )
		{
			$lang 		= JFactory::getLanguage();
			$extension 	= 'plg_' . $pluginType . '_' . $pluginName;
			
			// Load language file
			FieldsandfiltersFactory::getExtensions()->loadLanguage( $extension, JPATH_ADMINISTRATOR );
			
			
			
			// Get the database object and a new query object.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select( array(
				       $db->quoteName( 'element' ),
				       $db->quoteName( 'name' )
				) )
				->from( $db->quoteName( '#__extensions', 'e' ) )
				->where( $db->quoteName( 'e.client_id' ) . ' = ' . 0 )
				->where( $db->quoteName( 'e.type' ) . ' = ' . $db->quote( 'template' ) )
				->where( $db->quoteName( 'e.enabled' ) . ' = 1');

			//if( $template )
			//{
			//	$query->where('e.element = ' . $db->quote($template));
			//}

			//if ($template_style_id)
			//{
			//	$query->join('LEFT', '#__template_styles as s on s.template=e.element')
			//		->where('s.id=' . (int) $template_style_id);
			//}

			// Set the query and load the templates.
			$db->setQuery($query);
			$templates = $db->loadObjectList( 'element' );

			// Build the search paths for module layouts.
			$plugin_path = JPath::clean( JPATH_PLUGINS . '/' . $pluginType . '/' . $pluginName . '/tmpl' );
			
			// Prepare array of component layouts
			$plugin_layouts = array();

			// Prepare the grouped list
			$groups = array();

			// Add the layout options from the module path.
			if( is_dir( $plugin_path ) && ( $plugin_layouts = JFolder::files( $plugin_path, '^' . $modeName . '-[^_]*\.php$' ) ) )
			{
				// Create the group for the plgin
				$groups['_'] = array();
				$groups['_']['id'] = $this->id . '__';
				$groups['_']['text'] = JText::sprintf( 'FIELDSANDFILTERS_OPTION_FROM_PLUGIN' );
				$groups['_']['items'] = array();

				foreach( $plugin_layouts as $file)
				{
					// Add an option to the module group
					$value	= basename( $file, '.php' );
					$text 	= str_replace( ( $modeName . '-' ), '', $value );
					$text 	= $lang->hasKey( $key = strtoupper( $extension . '_LAYOUT_' . $value ) ) ? JText::_( $key ) : $text;
					$groups['_']['items'][] = JHtml::_( 'select.option', '_:' . $value, $text );
				}
			}

			// Loop on all templates
			if( $templates )
			{
				foreach( $templates as $template )
				{
					// Load language file
					FieldsandfiltersFactory::getExtensions()->loadLanguage( 'tpl_' . $template->element, JPATH_SITE );
					
					$template_path = JPath::clean( JPATH_SITE . '/templates/' . $template->element . '/html/' . $extension );

					// Add the layout options from the template path.
					if( is_dir( $template_path ) && ( $files = JFolder::files( $template_path, '^' . $modeName . '-[^_]*\.php$' ) ) )
					{
						foreach( $files as $i => $file )
						{
							// Remove layout that already exist in component ones
							if( in_array( $file, $plugin_layouts ) )
							{
								unset( $files[$i] );
							}
						}

						if( count( $files ) )
						{
							// Create the group for the template
							$groups[$template->element] = array();
							$groups[$template->element]['id'] = $this->id . '_' . $template->element;
							$groups[$template->element]['text'] = JText::sprintf( 'JOPTION_FROM_TEMPLATE', $template->name );
							$groups[$template->element]['items'] = array();

							foreach( $files as $file )
							{
								// Add an option to the template group
								$value = basename( $file, '.php' );
								$text 	= str_replace( ( $modeName . '-' ), '', $value );
								$text = $lang->hasKey( $key = strtoupper('TPL_' . $template->element . '_' . $extension . '_LAYOUT_' . $value ) )
									? JText::_($key) : $text;
								$groups[$template->element]['items'][] = JHtml::_( 'select.option', $template->element . ':' . $value, $text );
							}
						}
					}
				}
			}
			
			// Compute attributes for the grouped list
			$attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
			
			// Prepare HTML code
			$html = array();

			// Compute the current selected values
			$selected = array($this->value);

			// Add a grouped list
			$html[] = JHtml::_(
				'select.groupedlist', $groups, $this->name,
				array('id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => $selected)
			);

			return implode($html);
		}
		else
		{

			return '';
		}
	}
}
