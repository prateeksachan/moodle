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
 * Global Search install readme file when PHP Solr extension is not installed.
 *
 * @package   search
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$url = new moodle_url('/search/install.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('globalsearch', 'search'));
$PAGE->set_heading(get_string('globalsearch', 'search'));
$PAGE->set_url($url);

require_login();

echo $OUTPUT->header();
echo $OUTPUT->box_start();
$info = file_get_contents($CFG->dirroot . '/search/readme.md');
echo markdown_to_html($info);
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

exit();
