<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Buffer;

defined('_JEXEC') or die;

/**
 * Buffer Interface
 *
 * @package     Kextensions
 * @since       2.0
 *
 * [TODO] add PHPDock Blocks
 */
interface BufferInterface
{
    /**
     * Apply a rule to prepare query condition.
     */
    const IS = 'condition';

    /**
     * Apply a rule to prepare query *not* condition.
     */
    const IS_NOT = 'conditionNot';

    public function get();
}