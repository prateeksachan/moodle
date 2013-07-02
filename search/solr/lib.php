<?php

/** Solr Wraper class
 *  Listing down the exposed functions of SolrClient class to be used here in Global Search
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

    public function addDocument(SolrInputDocument $doc){
        return $this->client->addDocument($doc);
    }

    public function commit(){
        return $this->client->commit();
    }

    public function optimize(){
        return $this->client->optimize();
    }

    public function query(SolrParams $query){
        return $this->client->query($query);
    }

    public function deleteById($id){
        return $this->client->deleteById($id);
    }

    public function deleteByQuery($query){
        return $this->client->deleteByQuery($query);
    }

}

function solr_check_server(SolrWrapper $client) {
    if (!$client->ping()) {
    exit ('Solr service not responding');
    }
}
