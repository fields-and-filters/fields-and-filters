<?php
/**
 * Unit test runner bootstrap file for the Joomla Framework.
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

// Fix magic quotes.
@ini_set('magic_quotes_runtime', 0);

// Maximise error reporting.
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

define('_JEXEC', 1);

if (!defined('_JDEFINES'))
{
    define('JPATH_BASE', realpath(dirname(__DIR__)));
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';
JLoader::registerNamespace('Kextensions', JPATH_LIBRARIES);

// Mark afterLoad in the profiler.
// JDEBUG ? $_PROFILER->mark('afterLoad') : null;

// Instantiate the application.
// $app = JFactory::getApplication('site');

// Execute the application.
// $app->execute();