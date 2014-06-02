<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Rule\Rule;

use Kextensions\Rule\AbstractRule;

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

    public function conditionIs($value)
    {
        if (is_array($value))
        {
            return sprintf(' IN(%s) ', implode(',', $value));
        }

        return sprintf(' = %s ', $value);
    }

    public function conditionNot($value)
    {
        if (is_array($value))
        {
            return sprintf(' NOT IN(%s) ', implode(',', $value));
        }

        return sprintf(' != %s ', $value);
    }

}