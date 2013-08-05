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
    solr_set_query($query, $data);
    solr_prepare_filter($client, $data);
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
    if (!empty($data->searchfromtime) or !empty($data->searchtilltime)) {
        if (empty($data->searchfromtime)) {
            $data->searchfromtime = '*';
        } else {
            $data->searchfromtime = gmdate('Y-m-d\TH:i:s\Z', $data->searchfromtime);
        }
        if (empty($data->searchtilltime)) {
            $data->searchtilltime = '*';
        } else {
            $data->searchtilltime = gmdate('Y-m-d\TH:i:s\Z', $data->searchtilltime);
        }

        $query->addFilterQuery('modified:[' . $data->searchfromtime . ' TO ' . $data->searchtilltime . ']');
    }

    try {
        return solr_query_response($client, $client->query($query));
    } catch (SolrClientException $ex) {
        echo 'Please start the Solr server!';
    }
}

function solr_set_query($query, $data) {
    solr_set_highlight($query);
    $query->setQuery($data->queryfield);
    $query->setStart(SEARCH_SET_START);
    $query->setRows(SEARCH_SET_ROWS);
}

function solr_set_highlight($query) {
    $query->setHighlight(true);
    $highlightfields = array('content', 'user', 'author', 'name', 'title', 'intro');
    foreach ($highlightfields as $field) {
        $query->addHighlightField($field);
    }
    $query->setHighlightFragsize(SEARCH_SET_FRAG_SIZE);
    $query->setHighlightSimplePre('<span class="highlight">');
    $query->setHighlightSimplePost('</span>');
}

function solr_prepare_filter(SolrWrapper $client, $data) {
    if (!empty($data->titlefilterqueryfield)) {
        $data->titlefilterqueryfield = 'title:' . $data->titlefilterqueryfield;
    }
    if (!empty($data->authorfilterqueryfield)) {
        $data->authorfilterqueryfield = 'author:' . $data->authorfilterqueryfield;
    }
    if (!empty($data->modulefilterqueryfield)) {
        $data->modulefilterqueryfield = 'module:' . $data->modulefilterqueryfield;
    }
}

function solr_add_fields($query) {
    $fields = array('id', 'user', 'created', 'modified', 'author', 'name', 'title', 'intro',
                    'content', 'courseid', 'mime', 'contextlink', 'directlink', 'module');

    foreach ($fields as $field) {
        $query->addField($field);
    }
}

function solr_add_highlight_content($response) {
    $highlightedobject = $response->highlighting;
    foreach ($response->response->docs as $doc) {
        $x = $doc->id;
        $highlighteddoc = $highlightedobject->$x;
        solr_merge_highlight_field_values($doc, $highlighteddoc);
    }
}

function solr_merge_highlight_field_values($doc, $highlighteddoc) {
    $fields = array('content', 'user', 'author', 'name', 'title', 'intro');

    foreach ($fields as $field) {
        if (!empty($doc->$field)) {
            switch ($field) {
                case 'author':
                    if (!empty($highlighteddoc->$field)) {
                        $doc->$field = $highlighteddoc->$field;
                    }
                    break;

                default:
                    if (empty($highlighteddoc->$field)) {
                        $doc->$field = substr($doc->$field, 0, SEARCH_SET_FRAG_SIZE);
                    } else {
                        $doc->$field = reset($highlighteddoc->$field);
                    }
                    break;
            }
        }
    }
}

function solr_query_response(SolrWrapper $client, $query_response) {
    global $CFG;

    $response = $query_response->getResponse();
    $totalnumfound = $response->response->numFound;
    $docs = $response->response->docs;
    $numgranted = 0;

    if (!empty($totalnumfound)) {
        solr_add_highlight_content($response);
        foreach ($docs as $key => $value) {
            $solr_id = explode('_', $value->id);
            $modname = 'gs_support_' . $solr_id[0];
            // Check whether the module belonging to search response's Solr Document is gs_supported or not.
            if (!empty($CFG->$modname)) {
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
function solr_primary_filter() {
    global $USER;
    $primary_f = '';
    $courses = enrol_get_all_users_courses($USER->id);
    if (!empty($courses)) {
        $courseid = array();
        foreach ($courses as $key => $value) {
            $courseid[] = $value->id;
        }
        $primary_f = 'courseid: ' . implode(',', $courseid);
    }

    return $primary_f;
}
