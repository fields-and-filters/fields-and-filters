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
 * RuleInterface
 *
 * @package     Kextensions
 * @since       2.0
 */
interface RuleInterface
{
    public function prepare($data, $field);

    public function getValue();

    public function is();

    public function isNot();

    public function queryIs();

    public function queryNot();
}