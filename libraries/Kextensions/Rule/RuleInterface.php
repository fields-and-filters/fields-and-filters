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
 * Rule Interface
 *
 * @package     Kextensions
 * @since       2.0
 */
interface RuleInterface
{
    /**
     * Prepare the rule for reuse.
     *
     * @param object $data The full set of data to be filtered.
     * @param string $field The field to be filtered within the data.
     *
     * @return RuleInterface Current instance.
     */
    public function prepare($data, $field);

    /**
     * Get the value of the field being filtered, or null if the field is not set in the data.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Is the value valid?
     *
     * @return bool True if valid, false if not valid.
     */
    public function is();

    /**
     * Is the value *not* valid?
     *
     * @return bool True if not valid, false if valid.
     */
    public function isNot();

    /**
     * Prepare query condition.
     *
     * @return string
     */
    public function condition();

    /**
     * Prepare query *not* condition.
     *
     * @return string
     */
    public function conditionNot();
}