<?php

/* @var $DB mysqli_native_moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */
?>
<?php

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

}

function solr_check_server(SolrWrapper $client) {
    if (!$client->ping()) {
    exit ('Solr service not responding');
    }
}
