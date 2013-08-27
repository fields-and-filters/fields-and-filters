<?php
/**
 * @version     1.1.1
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extension.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
* @since       1.0.0
*/
class plgFieldsandfiltersExtensionsContentInstallerScript
{
        /**
         * Called on installation
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         *
         * @return  boolean  True on success
         * @since       1.0.0
         */
        public function install( JAdapterInstance $adapter )
        {
		self::_checkExtensionName();
		
		return true;
        }
	
	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 * @since       1.0.0
	 */
	public function update( JAdapterInstance $adapter )
	{
		self::_checkExtensionName();
		
		return true;
	}
	
	/**
	 * @since       1.0.0
	 */
	protected static function _checkExtensionName()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( $db->quoteName( 'extension_type_id' ) );
		$query->from( $db->quoteName( '#__fieldsandfilters_extensions_type' ) );
		$query->where( $db->quoteName( 'extension_name' ) . '=' . $db->quote( 'content' ) );
		$db->setQuery( $query );
                
		if( !$db->loadResult() )
		{
                        $query->clear();
			$query->insert( $db->quoteName( '#__fieldsandfilters_extensions_type' ) );
			$query->set( $db->quoteName( 'extension_name' ) . '=' . $db->quote( 'content' ) );
			$db->setQuery( $query );
			$db->execute();
		}
	}
}