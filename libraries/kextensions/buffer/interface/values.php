<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('JPATH_PLATFORM') or die;

/**
 * KextensionsBufferInterfaceValues.
 *
 * @since       1.0.0
 */
interface KextensionsBufferInterfaceValues
{
	/**
	 * @since       1.0.0
	 */
	public function getForeignName();

	/**
	 * @since       1.0.0
	 */
	public function getValuesName();
}
