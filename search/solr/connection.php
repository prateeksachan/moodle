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
 * Connection settings for Solr PHP extension.
 *
 * @package    Global Search
 * @subpackage solr
 * @copyright  Prateek Sachan {@link http://prateeksachan.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/search/' . $CFG->search_engine . '/lib.php');
require_once($CFG->dirroot . '/search/' . $CFG->search_engine . '/search.php');

if (function_exists('solr_get_version')) {
    // Solr connection options.
    $options = array(
        'hostname' => $CFG->solr_server_hostname,
        'login'    => $CFG->solr_server_username,
        'password' => $CFG->solr_server_password,
        'port'     => $CFG->solr_server_port,
        'issecure' => $CFG->solr_secure,
        'ssl_cert' => $CFG->solr_ssl_cert,
        'ssl_cert_only' => $CFG->solr_ssl_cert_only,
        'ssl_key' => $CFG->solr_ssl_key,
        'ssl_password' => $CFG->solr_ssl_keypassword,
        'ssl_cainfo' => $CFG->solr_ssl_cainfo,
        'ssl_capath' => $CFG->solr_ssl_capath
    );

    // If php solr extension 1.0.3-alpha installed, one may choose 3.x or 4.x solr from admin settings page.
    if (solr_get_version() == '1.0.3-alpha') {
        if ($CFG->solr_version == '4.0') {
            $object = new SolrClient($options, $CFG->solr_version);
        } else {
            $object = new SolrClient($options, '3.0');
        }
    } else { // No choice if php solr extension <=1.0.2 is installed.
        $object = new SolrClient($options);
    }
    $client = new global_search_engine($object);
}
