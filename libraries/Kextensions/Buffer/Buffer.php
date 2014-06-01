<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Buffer;

use Kextensions\Object\Object;
use Kextensions\Filter\Filter;

defined('_JEXEC') or die;

/**
 * Buffer
 *
 * @package     Kextensions
 * @since       2.0
 */
abstract class Buffer extends Object
{
    protected $rules = array();

    abstract protected function load();

    public function addFilter($field, $value)
    {
        $filter[] = array(
            'field' => $field,
            'value' => $value
        );

        return $this;
    }




}