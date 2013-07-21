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
 * Running the Solr server according to admin settings. 
 * Throws an error if the solr server is not running
 *
 * @package   search
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/search/' . $CFG->SEARCH_ENGINE . '/lib.php');

if ($CFG->SEARCH_ENGINE == 'solr') {

    // Solr connection options.
    $options = array(
        'hostname' => $CFG->SOLR_SERVER_HOSTNAME,
        'login'    => $CFG->SOLR_SERVER_USERNAME,
        'password' => $CFG->SOLR_SERVER_PASSWORD,
        'port'     => $CFG->SOLR_SERVER_PORT,
    );

    // If php solr extension 1.0.3-alpha installed, one may choose 3.x or 4.x solr from admin settings page.
    if (solr_get_version() == '1.0.3-alpha') {
        if ($CFG->SOLR_VERSION == '4.0') {
            $object = new SolrClient($options, $CFG->SOLR_VERSION);
        } else {
            $object = new SolrClient($options, '3.0');
        }
    } else { // No choice if php solr extension <=1.0.2 is installed.
        $object = new SolrClient($options);
    }

    $client = new SolrWrapper($object);

    solr_check_server($client);
}
