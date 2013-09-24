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
 * Global Search admin settings
 *
 * @package   Global Search
 * @copyright Prateek Sachan {@link http://prateeksachan.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/search/' . $CFG->SEARCH_ENGINE . '/connection.php');
require_once($CFG->dirroot . '/search/lib.php');

admin_externalpage_setup('statistics');
$PAGE->set_title(get_string('globalsearch', 'search'));
$PAGE->set_heading(get_string('globalsearch', 'search'));

global $DB;

class search_admin_form extends moodleform {

    function definition() {
        $mform = & $this->_form;
        $checkboxarray = array();
        $checkboxarray[] =& $mform->createElement('checkbox', 'delete', '', get_string('delete', 'search'));
        $mform->addGroup($checkboxarray, 'indexcheckbox', '', array(' '), false);
        $mform->closeHeaderBefore('indexcheckbox');

        $modcheckboxarray = array();
        $mods = search_get_modules();
        $modcheckboxarray[] =& $mform->createElement('advcheckbox', 'all', '', 'Entire Index', array('group' => 1));
        foreach ($mods as $mod) {
            $modcheckboxarray[] =& $mform->createElement('advcheckbox', $mod->name, '', ucfirst($mod->name), array('group' => 2));
        }
        $mform->addGroup($modcheckboxarray, 'modadvcheckbox', '', array(' '), false);
        $mform->closeHeaderBefore('modadvcheckbox');

        $mform->disabledIf('modadvcheckbox', 'delete', 'notchecked');

        $this->add_action_buttons($cancel = false);
        $mform->setDefault('action', '');
    }
}

require_capability('moodle/site:config', context_system::instance());

$search_engine_installed = $CFG->SEARCH_ENGINE . '_installed';
if (!$search_engine_installed()) {
    include($CFG->dirroot . '/search/install.php');
    exit();
}

echo $OUTPUT->header();
echo $OUTPUT->heading('Last indexing statistics');

if (!$CFG->enableglobalsearch) {
    echo $OUTPUT->box_start();
    echo 'Global Search has been disabled';
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit();
}

$mform = new search_admin_form();

if ($data = $mform->get_data()) {
    if (!empty($data->delete)) {
        if (!empty($data->all)) {
            $data->module = null;
            search_delete_index($client, $data);
        } else {
            $a = '';
            foreach ($data as $key => $value) {
                if ($value && $key!='delete' && $key!='submitbutton') {
                    $a .= $key . ',';
                }
            }
            $data->module = substr($a, 0, -1);
            search_delete_index($client, $data);
        }
    }
}

$gstable = new html_table();
$gstable->id = 'gs-control-panel';
$gstable->head = array(
    'Name', 'Newest document indexed', 'Last run <br /> (time, # docs, # records, # ignores)');
$gstable->colclasses = array(
    'displayname', 'lastrun', 'timetaken'
);

$mods = search_get_iterators(false);
$config = search_get_config(array_keys($mods));

foreach ($mods as $name => $mod) {
    $cname = new html_table_cell(ucfirst($name));
    $clastrun = new html_table_cell($config[$name]->lastindexrun);
    $ctimetaken = new html_table_cell($config[$name]->indexingend - $config[$name]->indexingstart . ' , ' .
                                        $config[$name]->docsprocessed . ' , ' .
                                        $config[$name]->recordsprocessed . ' , ' .
                                        $config[$name]->docsignored);
    $modname = 'gs_support_' . $name;
    //$cactive = new html_table_cell(($CFG->$modname) ? 'Yes' : 'No');
    $row = new html_table_row(array($cname, $clastrun, $ctimetaken));
    $gstable->data[] = $row;
}

echo html_writer::table($gstable);
echo $OUTPUT->container_start();
echo $OUTPUT->box_start();

$search_engine_check_server = $CFG->SEARCH_ENGINE . '_check_server';
if (!$search_engine_check_server($client)) {
    echo 'Solr Server is not running!';
} else {
    echo $mform->display();
}

echo $OUTPUT->box_end();
echo $OUTPUT->container_end();
echo $OUTPUT->footer();
