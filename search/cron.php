<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Global Search cron() for indexing.
 *
 * @package   Global Search
 * @copyright Prateek Sachan {@link http://prateeksachan.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once('../config.php');
require_once($CFG->dirroot . '/search/' . $CFG->SEARCH_ENGINE . '/connection.php');
require_once($CFG->dirroot . '/search/lib.php');

$search_engine_installed = $CFG->SEARCH_ENGINE . '_installed';
$search_engine_check_server = $CFG->SEARCH_ENGINE . '_check_server';

if ($search_engine_installed() and $search_engine_check_server($client)) {
    // Indexing database records for modules + rich documents of forum.
    search_index($client);
    // Indexing rich documents for lesson, wiki.
    search_index_files($client);
    // Optimize index at last.
    search_optimize_index($client);
}
