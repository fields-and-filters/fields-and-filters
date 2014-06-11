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
    /**
     * Validates that the value is equals.
     *
     * @param mixed $value The value for comparison.
     *
     * @return bool True if valid, false if not.
     */
    public function validate($value)
    {
        return $this->getValue() == $value;
    }

    /**
     * Prepare formula for query.
     * If value is number/string then formula use *=* equals
     * If value is array then formula use *IN()*
     *
     * @param mixed $value The value insert into the formula.
     *
     * @return string
     */
    public function formula($value)
    {
        if (is_array($value))
        {
            return sprintf(' IN(%s) ', implode(',', $value));
        }

        return sprintf(' = %s ', $value);
    }

    /**
     * Prepare *not* formula for query.
     * If value is number/string then formula use *<>* equals
     * If value is array then formula use *NOT IN()*
     *
     * @param mixed $value The value insert into the formula.
     *
     * @return string
     */
    public function formulaNot($value)
    {
        if (is_array($value))
        {
            return sprintf(' NOT IN(%s) ', implode(',', $value));
        }

        return sprintf(' <> %s ', $value);
    }

}