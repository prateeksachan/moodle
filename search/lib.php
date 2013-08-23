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
 * Global Search library code
 *
 * @package   search
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/lib/accesslib.php');

define('SEARCH_INDEX_PATH', $CFG->dataroot . '/search');
define('SEARCH_TYPE_HTML', 1);
define('SEARCH_TYPE_TEXT', 2);
define('SEARCH_TYPE_FILE', 3);

define('SEARCH_ACCESS_DENIED', 0);
define('SEARCH_ACCESS_GRANTED', 1);
define('SEARCH_ACCESS_DELETED', 2);

define('SEARCH_MAX_RESULTS', 100);
define('SEARCH_SET_START', 0);
define('SEARCH_SET_ROWS', 1000);
define('SEARCH_SET_FRAG_SIZE', 500);


/**
 * Modules activated for Global Search.
 * @param boolean $requireconfig->whether to check if the admin has ac/de-vated a particular module. Useful in clearing index. 
 * @return array $mods
 */
function search_get_modules($requireconfig = true) {
    global $CFG, $DB;
    $mods = $DB->get_records('modules', null, 'name', 'id,name');
    foreach ($mods as $key => $mod) {
        $modname = 'gs_support_' . $mod->name;
        if ($requireconfig) {
            if (empty($CFG->$modname) or !plugin_supports('mod', $mod->name, FEATURE_GLOBAL_SEARCH)) {
                unset($mods[$key]);
            }
        } else {
            if (!plugin_supports('mod', $mod->name, FEATURE_GLOBAL_SEARCH)) {
                unset($mods[$key]);
            }
        }
    }
    return $mods;
}

/**
 * Search API functions for modules.
 * @return stdClass object $functions
 */
function search_get_iterators() {
    global $CFG;
    $mods = search_get_modules(true);
    $functions = array();
    foreach ($mods as $mod) {
        if (file_exists("$CFG->dirroot/mod/{$mod->name}/lib.php")) {
            include_once("$CFG->dirroot/mod/{$mod->name}/lib.php");
            if (!function_exists($mod->name . '_search_iterator')) {
                throw new coding_exception('Module supports GLOBAL_SEARCH but function \'' .
                                            $mod->name . '_search_iterator' . '\' is missing.');
            }
            if (!function_exists($mod->name . '_search_get_documents')) {
                throw new coding_exception('Module supports GLOBAL_SEARCH but function \'' .
                                            $mod->name . '_search_get_documents' . '\' is missing.');
            }
            if (!function_exists($mod->name . '_search_access')) {
                throw new coding_exception('Module supports GLOBAL_SEARCH but function \'' .
                                            $mod->name . '_search_access' . '\' is missing.');
            }
            $functions[$mod->name] = new stdClass();
            $functions[$mod->name]->iterator = $mod->name . '_search_iterator';
            $functions[$mod->name]->documents = $mod->name . '_search_get_documents';
            $functions[$mod->name]->access = $mod->name . '_search_access';
            $functions[$mod->name]->module = $mod->name;
        } else {
            throw new coding_exception('Library file for module \'' . $mod->name . '\' is missing.');
        }
    }

    return $functions;
}

/**
 * Merge separate index segments into one.
 * @param SolrWrapper $client
 */
function search_optimize_index(SolrWrapper $client) {
    $client->optimize();
}

/**
 * Index all documents.
 * @param SolrWrapper $client
 */
function search_index(SolrWrapper $client) {
    set_time_limit(576000);
    $iterators = search_get_iterators();
    foreach ($iterators as $name => $iterator) {
        mtrace('Processing module ' . $iterator->module);
        $indexingstart = time();
        $iterfunction = $iterator->iterator;
        $getdocsfunction = $iterator->documents;
        $lastindexrun = get_config('search', $name . '_lastindexrun');
        $recordset = $iterfunction($lastindexrun);
        $numrecords = 0;
        $numdocs = 0;
        $numdocsignored = 0;
        foreach ($recordset as $record) {
            ++$numrecords;
            $timestart = microtime(true);
            $documents = $getdocsfunction($record->id);

            foreach ($documents as $solrdocument) {
                switch (($solrdocument->getField('type')->values[0])) {
                    case SEARCH_TYPE_HTML:
                        $client->add_document($solrdocument);
                        ++$numdocs;
                        break;
                    default:
                        ++$numdocsignored;
                        throw new search_ex('Incorrect document format encountered');
                }
            }
            $timetaken = microtime(true) - $timestart;
        }
        $recordset->close();
        if ($numrecords > 0) {
            $client->commit();
            $indexingend = time();
            set_config($name . '_indexingstart', $indexingstart, 'search');
            set_config($name . '_indexingend', $indexingend, 'search');
            set_config($name . '_lastindexrun', $record->modified, 'search');
            set_config($name . '_docsignored', $numdocsignored, 'search');
            set_config($name . '_docsprocessed', $numdocs, 'search');
            set_config($name . '_recordsprocessed', $numrecords, 'search');
            mtrace("Processed $numrecords records containing $numdocs documents for " . $iterator->module . '. Commits completed.');
        }
    }
}

/**
 * Index all Rich Document files.
 * @param SolrWrapper $client
 */
function search_index_files(SolrWrapper $client) {
    global $CFG;
    set_time_limit(576000);
    $mod_file = array(
                'lesson' => 'lesson',
                'wiki' => 'wiki'
                );

    foreach ($mod_file as $mod => $name) {
        $modname = 'gs_support_' . $name;
        if (empty($CFG->$modname)) {
            unset($mod_file[$mod]);
        }
    }

    mtrace("Memory usage:" . display_size(memory_get_usage()));
    $timestart = microtime(true);

    foreach ($mod_file as $mod => $name) {
        mtrace('Indexing files for module ' . $name);
        $lastindexrun = search_get_config_file($name);
        $indexfunction = $name . '_search_files';
        // This the the indexing function for indexing rich documents. config settings will be updated inside this function only.
        $indexfunction($lastindexrun);
    }
    $timetaken = microtime(true) - $timestart;
    mtrace("Time : $timetaken");
    $client->commit();
}


/**
 * Resets config_plugin table after index deletion as re-indexing will be done from start.
 * optional @param string $s containing modules whose index was chosen to be deleted.
 */
function search_reset_config($s = null) {
    if (!empty($s)) {
        $mods = explode(',', $s);
    } else {
        $get_mods = search_get_modules();
        $mods = array();
        foreach ($get_mods as $mod) {
            $mods[] = $mod->name;
        }
    }
    foreach ($mods as $key => $name) {
        set_config($name . '_indexingstart', 0, 'search');
        set_config($name . '_indexingend', 0, 'search');
        set_config($name . '_lastindexrun', 0, 'search');
        set_config($name . '_docsignored', 0, 'search');
        set_config($name . '_docsprocessed', 0, 'search');
        set_config($name . '_recordsprocessed', 0, 'search');
        if ($name == 'wiki') { // Extra config setting reset for wiki rich documents.
            set_config($name . '_lastindexedfilerun', 0, 'search');
        }
    }
}

/**
 * Deletes index.
 * @param SolrWrapper $client
 * @param stdClass object $data
 */
function search_delete_index(SolrWrapper $client, $data) {
    if (!empty($data->module)) {
        $client->delete_by_query('module:' . $data->module);
        search_reset_config($data->module);
    } else {
        $client->delete_by_query('*:*');
        search_reset_config();
    }
    $client->commit();
}

/**
 * Deletes index by id.
 * @param SolrWrapper $client object
 * @param Solr Document string $id
 */
function search_delete_index_by_id(SolrWrapper $client, $id) {
    $client->delete_by_id($id);
    $client->commit();
}

/**
 * Returns Global Search configuration settings from config_plugin table.
 * @param array $mods
 * @return array $configsettings
 */
function search_get_config($mods) {
    $allconfigs = get_config('search');
    $vars = array('indexingstart', 'indexingend', 'lastindexrun', 'docsignored', 'docsprocessed', 'recordsprocessed');

    $configsettings =  array();
    foreach ($mods as $mod) {
        $configsettings[$mod] = new stdClass();
        foreach ($vars as $var) {
            $name = "{$mod}_$var";
            if (!empty($allconfigs->$name)) {
                $configsettings[$mod]->$var = $allconfigs->$name;
            } else {
                $configsettings[$mod]->$var = 0;
            }
        }
        if (!empty($configsettings[$mod]->lastindexrun)) {
            $configsettings[$mod]->lastindexrun = userdate($configsettings[$mod]->lastindexrun);
        } else {
            $configsettings[$mod]->lastindexrun = "Never";
        }
    }
    return $configsettings;
}

/**
 * Returns Global Search iterator setting for indexing files.
 * @param string $mod
 * @return string setting value
 */
function search_get_config_file($mod) {
    switch ($mod) {
        case 'lesson':
            return get_config('search', $mod . '_lastindexrun');

        case 'wiki':
            return get_config('search', $mod . '_lastindexedfilerun');

        default:
            return 0;
    }
}

/** 
 * Builds the cURL object's url for indexing Rich Documents
 * @return string $url
 */
function search_curl_url() {
    global $CFG;
    $url = $CFG->SOLR_SERVER_HOSTNAME . ':' . $CFG->SOLR_SERVER_PORT . '/solr/update/extract?';
    return $url;
}

/** 
 * Displaying search results 
 * @param stdClass object $result containing a single search response to be displayed
 */
function search_display_results($result) {
    global $DB, $OUTPUT;
    $OUTPUT->box_start();

    $doc_id = $result->id;
    $course = $DB->get_record('course', array('id' => $result->courseid), 'fullname', MUST_EXIST);

    $coursefullname = $course->fullname;
    $attributes = array('target' => '_new');
    $s = '';
    $s .= html_writer::start_tag('div', array('class'=>'globalsearchpost clearfix side'));
    $s .= html_writer::start_tag('div', array('class'=>'row header clearfix'));
    $s .= html_writer::start_tag('div', array('class'=>'course'));
    $s .= html_writer::link(new moodle_url('/course/view.php?id=' . $result->courseid), $coursefullname, $attributes);
    $s .= ' > ' . ucfirst($result->module);
    $s .= html_writer::end_tag('div');
    $s .= html_writer::start_tag('div', array('class'=>'name'));
    if (!empty($result->name)) {
        $s .=html_writer::link(new moodle_url($result->contextlink), $result->name, $attributes);
    }
    if (!empty($result->intro)) {
        $s .= html_writer::span(' Description: ' . $result->intro, 'description');
    }
    $s .= html_writer::end_tag('div');
    $s .= html_writer::start_tag('div', array('class'=>'author'));
    if (!empty($result->user)) {
        $s .='<b><i>By: </i></b>';
        $s .= html_writer::link(search_get_user_url($result->user), $result->user , $attributes);
    }
    if (!empty($result->author)) {
        $s .='<b>Document Author(s): </b>';
        foreach ($result->author as $key => $value) {
            $author_url = search_get_user_url($value);
            if (!empty($author_url)) {
                $s .= html_writer::link($author_url, $value, $attributes) . ', ';
            } else {
                $s .= $value . ', ';
            }
        }
        $s =rtrim($s, ', ');
        $s .='<br>';
    }
    $s .= html_writer::end_tag('div');
    $s .= html_writer::start_tag('div', array('class'=>'timeinfo'));
    if (!empty($result->modified)) {
        $s .='<b><i>Last Modified on: </i></b>' . userdate(strtotime($result->modified)) . ' ';
    }
    if (!empty($result->created)) {
        $s .='<b><i>Created on: </i></b>' . userdate(strtotime($result->created)) . '<br/>';
    }
    $s .= html_writer::end_tag('div');
    $s .= html_writer::end_tag('div');
    $s .= html_writer::start_tag('div', array('class'=>'row maincontent clearfix'));
    $s .= html_writer::start_tag('div', array('class'=>'content'));
    if (!empty($result->title)) {
        $s .=$result->title . '<br/>';
    }
    if (!empty($result->content)) {
        $s .=$result->content . '<br/>';
    }
    $s .= html_writer::end_tag('div');
    $s .= html_writer::end_tag('div');
    $s .= html_writer::start_tag('div', array('class'=>'row footer clearfix side'));

    if (!empty($result->directlink)) {
        $s .= html_writer::span(
                                html_writer::link(new moodle_url($result->directlink), 'Direct link to file', $attributes),
                                'urllink');
    } else if (!empty($result->contextlink)) {
        $s .= html_writer::span(
                                html_writer::link(new moodle_url($result->contextlink), 'View this result in context', $attributes),
                                'urllink');
    }
    $s .= html_writer::end_tag('div');
    $s .= html_writer::end_tag('div'); // End.

    echo $s;
    $OUTPUT->box_end();
}

/** 
 * Searches the user table for userid
 * @param string name of user
 * @return string $url of the user's profile
 */
function search_get_user_url($fullname) {
    global $DB;
    $url = '';
    try {
        $username = explode(' ', $fullname);
        if (count($username) == 2) {
            $userdata = $DB->get_record('user', array('firstname' => $username[0], 'lastname' => $username[1]), 'id', MUST_EXIST);
            $url = new moodle_url('/user/profile.php?id=' . $userdata->id);
        }
    } catch (dml_missing_record_exception $ex) {
        return $url;
    }
    return $url;
}
