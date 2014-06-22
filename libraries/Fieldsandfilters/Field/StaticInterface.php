<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldsandfilters\Field;

use Fieldsandfilters\Content\AbstractContent;

defined('_JEXEC') or die;

/**
 * Static Interface
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
interface StaticInterface extends BaseInterface
{
    const _CLASS_ = __CLASS__;

    public function setData($data);

    public function getData();
}