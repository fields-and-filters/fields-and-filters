<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

defined('_JEXEC') or die;

/**
 * Collection Interface
 *
 * @package     Kextensions
 * @since       2.0
 */
interface CollectionInterface extends ObjectInterface
{
    public function first();

    public function last();

    public function map();

    public function filter();

    public function clear();

//    public function find();
}