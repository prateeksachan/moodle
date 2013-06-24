<?php

/**
 * Global Search cron() function
 * for indexing
 */

require_once('../config.php');

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/search/connection.php');
require_once($CFG->dirroot . '/search/lib.php');
    
search_index($client);