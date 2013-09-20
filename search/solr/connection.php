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

require_once($CFG->dirroot . '/search/' . $CFG->SEARCH_ENGINE . '/lib.php');
require_once($CFG->dirroot . '/search/' . $CFG->SEARCH_ENGINE . '/search.php');

if (function_exists('solr_get_version')) {
    // Solr connection options.
    $options = array(
        'hostname' => $CFG->SOLR_SERVER_HOSTNAME,
        'login'    => $CFG->SOLR_SERVER_USERNAME,
        'password' => $CFG->SOLR_SERVER_PASSWORD,
        'port'     => $CFG->SOLR_SERVER_PORT,
        'issecure' => $CFG->SOLR_SECURE,
        'ssl_cert' => $CFG->SOLR_SSL_CERT,
        'ssl_cert_only' => $CFG->SOLR_SSL_CERT_ONLY,
        'ssl_key' => $CFG->SOLR_SSL_KEY,
        'ssl_password' => $CFG->SOLR_SSL_KEYPASSWORD,
        'ssl_cainfo' => $CFG->SOLR_SSL_CAINFO,
        'ssl_capath' => $CFG->SOLR_SSL_CAPATH
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
    $client = new global_search_engine($object);
}
