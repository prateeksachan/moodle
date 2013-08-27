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
 * Global Search install readme
 *
 * @package   search
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('installglobalsearch');
$PAGE->set_title(get_string('installglobalsearch', 'admin'));
$PAGE->set_heading(get_string('installglobalsearch', 'admin'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('globalsearch', 'search'));
echo $OUTPUT->box_start();

$info = file_get_contents($CFG->dirroot . 'search/solr/readme.md');
echo markdown_to_html($info);
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
