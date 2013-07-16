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
 * Global Search solr search functions
 *
 * @package   search
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/enrollib.php');

function solr_display_search_form($mform) {
    $mform->display();
}

function solr_execute_query(SolrWrapper $client, $data) {
    $query = new SolrQuery();
    $query->setQuery($data->queryfield);
    $query->setStart(SEARCH_SET_START);
    $query->setRows(SEARCH_SET_ROWS);
    solr_add_fields($query);

    if (!empty($data->titlefilterqueryfield)) {
        $query->addFilterQuery($data->titlefilterqueryfield);
    }
    if (!empty($data->authorfilterqueryfield)) {
        $query->addFilterQuery($data->authorfilterqueryfield);
    }
    if (!empty($data->modulefilterqueryfield)) {
        $query->addFilterQuery($data->modulefilterqueryfield);
    }

    return solr_query_response($client, $client->query($query));
}

function solr_prepare_query(SolrWrapper $client, $data) {
    if (!empty($data->titlefilterqueryfield)) {
        $data->titlefilterqueryfield = 'title:' . $data->titlefilterqueryfield;
    }
    if (!empty($data->authorfilterqueryfield)) {
        $data->authorfilterqueryfield = 'author:' . $data->authorfilterqueryfield;
    }
    if (!empty($data->modulefilterqueryfield)) {
        $data->modulefilterqueryfield = 'module:' . $data->modulefilterqueryfield;
    }
    return solr_execute_query($client, $data);
}

function solr_add_fields($query) {
    $fields = array('type', 'id', 'user', 'created', 'modified', 'author', 'name', 'title', 'intro',
                    'content', 'courseid', 'mime', 'contextlink', 'directlink', 'filepath', 'module');

    foreach ($fields as $field) {
        $query->addField($field);
    }
}

function solr_query_response(SolrWrapper $client, $query_response) {
    global $CFG;
    $response = $query_response->getResponse();
    $totalnumfound = $response->response->numFound;
    $docs = $response->response->docs;
    $numgranted = 0;

    if (!empty($totalnumfound)) {
        foreach ($docs as $key => $value) {
            if (strpos($value->id,'file') !== false) {
                $value->id = str_replace('_file', '', $value->id);
            }
            $solr_id = explode("_", $value->id);
            $modname = 'gs_support_' . $solr_id[0];
            if (!empty($CFG->$modname)) { // check whether the module belonging to search response's Solr Document is gs_supported or not.
                if (file_exists("$CFG->dirroot/mod/{$solr_id[0]}/lib.php")) {
                    include_once("$CFG->dirroot/mod/{$solr_id[0]}/lib.php");
                } else {
                    throw new coding_exception('Library file for module \'' . $solr_id[0] . '\' is missing.');
                }
                $access_func = $solr_id[0] . '_search_access';
                $acc = $access_func($solr_id[1]);

                switch ($acc) {
                    case SEARCH_ACCESS_DELETED:
                        search_delete_index_by_id($client, $value->id);
                        unset($docs[$key]);
                        break;
                    case SEARCH_ACCESS_DENIED:
                        unset($docs[$key]);
                        break;
                    case SEARCH_ACCESS_GRANTED:
                        $numgranted++;
                        break;
                }
            } else {
                unset($docs[$key]);
            }

            if ($numgranted == SEARCH_MAX_RESULTS) {
                $docs = array_slice($docs, 0, SEARCH_MAX_RESULTS, true);
                break;
            }            
        }
    }
    
    return $docs;
}

// Initial solr filter by looking into enrolled courses - removed.
function solr_primary_filter(){
    global $USER;
    $primary_f = '';
    $courses = enrol_get_all_users_courses($USER->id);
    if (!empty($courses)){
        $courseid = array();
        foreach ($courses as $key => $value) {
            $courseid[] = $value->id;
        }
        $primary_f = 'courseid: ' . implode(',', $courseid);
    }

    return $primary_f;
}
