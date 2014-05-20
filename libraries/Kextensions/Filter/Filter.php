<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Filter;

defined('_KEXTENSIONS_') or die;

/**
 * Filter
 *
 * @package     Kextensions
 * @since       2.0
 */
class Filter extends \FilterIterator
{
    public function __construct(\Iterator $iterator , $filter)
    {
        parent::__construct($iterator);
        $this->userFilter = $filter;
    }

    public function accept()
    {
        $user = $this->getInnerIterator()->current();
        if( strcasecmp($user['name'],$this->userFilter) == 0) {
            return false;
        }
        return true;
    }
}