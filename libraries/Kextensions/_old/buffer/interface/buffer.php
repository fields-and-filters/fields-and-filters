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
 * KextensionsBufferInterfaceBuffer.
 *
 * @since       1.0.0
 */
interface KextensionsBufferInterfaceBuffer
{
	/**
	 * @since       1.0.0
	 */
	public function getTypeName();

	/**
	 * @since       1.0.0
	 */
	public function getPrimaryName();

	/**
	 * @since       1.0.0
	 */
	public function getStateName();
}
