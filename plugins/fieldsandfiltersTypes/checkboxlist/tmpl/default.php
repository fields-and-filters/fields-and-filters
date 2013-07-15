<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.textarea
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined( '_JEXEC' ) or die;

if( JFactory::getConfig()->get('debug') )
{
        $field  = $plugin->field;
        $layout = $field->params->get( 'type.field_layout' );
        echo JText::sprintf( 'PLG_FAF_TS_ERROR_TEMPLATE_NOT_EXISTS', $field->field_name, $layout );
}