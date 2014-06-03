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
 * Buffer
 *
 * @package     Kextensions
 * @since       2.0
 */
interface BufferInterface
{
    const IS = 'queryIs';

    const IS_NOT = 'queryNot';

    public function get();
}