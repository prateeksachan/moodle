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
 * Solr Wraper class
 * Listing down the exposed functions of SolrClient class to be used here in Global Search
 *
 * @package   search
 * @subpackage solr
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class SolrWrapper {
    private $client;

    public function __construct(SolrClient $object) {
        $this->client = $object;
    }

    /**
     * Exposed Functions of SolrClient Class
     */
    public function ping() {
        return $this->client->ping();
    }

    public function add_document(SolrInputDocument $doc) {
        return $this->client->addDocument($doc);
    }

    public function commit() {
        return $this->client->commit();
    }

    public function optimize() {
        return $this->client->optimize();
    }

    public function query(SolrParams $query) {
        return $this->client->query($query);
    }

    public function delete_by_id($id) {
        return $this->client->deleteById($id);
    }

    public function delete_by_query($query) {
        return $this->client->deleteByQuery($query);
    }

}

function solr_check_server(SolrWrapper $client) {
    try {
        $client->ping();
    } catch (SolrClientException $ex) {
        echo 'Please start the Solr server!';
    }
}
