<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Fieldsandfilters\Field;

use Kextensions\Object\CollectionInterface AS BaseCollectionInterface;

defined('_JEXEC') or die;

/**
 * Collection Interface
 *
 * @package     Kextensions
 * @since       2.0
 */
interface CollectionInterface extends BaseCollectionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param BaseInterface $value The object with instance of Object.
     */
    public function set($property, $value);
}