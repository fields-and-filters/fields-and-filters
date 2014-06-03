<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Rule;

defined('_JEXEC') or die;

/**
 * ManagerRuleInterface
 *
 * @package     Kextensions
 * @since       2.0
 */
interface ManagerRuleInterface
{
    /**
     * Add a rule.
     *
     * @param string $name The name of the rule to apply.
     * @param string $field The field for the rule.
     * @param string $method The rule method to use.
     */
    public function addRule($name, $field, $method);

    /**
     * Clear the collection of rules.
     */
    public function clearRule();
}