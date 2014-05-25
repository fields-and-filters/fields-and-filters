<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Filter;

use Kextensions\Filter\AbstractRule;

defined('_JEXEC') or die;

/**
 * Equals Rule
 *
 * @package     Kextensions
 * @since       2.0
 */
class Equals extends AbstractRule
{
    public function validate($value)
    {
        return $this->getValue() == $value;
    }
}