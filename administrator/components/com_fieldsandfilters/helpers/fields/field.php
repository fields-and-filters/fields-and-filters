<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * FieldsandfiltersFieldsField
 *
 * @package     com_fieldsandfilters
 * @since       1.2.0
 */
class FieldsandfiltersFieldsField
{
	/**
	 * @since       1.2.0
	 **/
	const PREPARE_CONTENT_FIELDS = 1;

	/**
	 * @since       1.2.0
	 **/
	const PREPARE_CONTENT_SYSTEM = 2;

	/**
	 * @since       1.2.0
	 **/
	public static function preparationContent($type, &$data, $context, $excluded = array(), JRegistry $params = null)
	{
		switch ($type)
		{
			case self::PREPARE_CONTENT_FIELDS :
				FieldsandfiltersFieldsHelper::preparationContent( &$data, $context, null, null, null, $excluded );
				break;
			case self::PREPARE_CONTENT_SYSTEM :
				JHtml::_('content.prepare', &$data, $params, $context);
				break;
		}
	}
}