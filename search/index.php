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
 * Display form for entering search queries
 *
 * @package   search
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once($CFG->dirroot . '/search/connection.php');
require_once($CFG->dirroot . '/search/lib.php');
require_once($CFG->dirroot . '/search/search.php');
require_once($CFG->dirroot . '/search/locallib.php');

$page = optional_param('page', 0, PARAM_INT);
$search = trim(optional_param('search', '', PARAM_NOTAGS));
$fq_module = trim(optional_param('fq_module', '', PARAM_NOTAGS));

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('globalsearch', 'search'));
$PAGE->set_heading(get_string('globalsearch', 'search'));

require_login();

$mform = new search_form();
$data = new stdClass();

if (!empty($search)) {
    $data->queryfield = $search;
    $data->modulefilterqueryfield = $fq_module;
    $mform->set_data($data);
	$results = solr_execute_query($client, $data);
}

if ($data = $mform->get_data()) {
	$search = $data->queryfield;
    $fq_module = $data->modulefilterqueryfield;
    $results = solr_execute_query($client, $data);
}

$urlparams = array('search' => $search, 'fq_module' => $fq_module, 'page' => $page);
$url = new moodle_url('/search/index.php', $urlparams);
$PAGE->set_url($url);

echo $OUTPUT->header();

if ($showreadme) { // Printing the Global Search wiki if solr-php extension is not installed.
    echo $OUTPUT->box_start();
    $info = file_get_contents($CFG->dirroot . '/search/readme.md');
    echo markdown_to_html($info);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit();
}

solr_check_server($client);

solr_display_search_form($mform);

if (!empty($results) and !is_array($results)) {
    echo $results;
}

if (!empty($results) and is_array($results)) {
    $perpage = DISPLAY_RESULTS_PER_PAGE;
    echo 'Total accessible records: ' . count($results);
    echo $OUTPUT->paging_bar(count($results), $page, $perpage, $url);
    $hits = array_slice($results, $page*$perpage, $perpage, true);
    foreach ($hits as $hit) {
        search_display_results($hit);
    }
    echo $OUTPUT->paging_bar(count($results), $page, $perpage, $url);
}

echo $OUTPUT->footer();
