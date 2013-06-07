<?php

/**
 * Connecting the Solr server as defined by parameters in bootstrap.php
 * Throws an error if the solr server is not running
**/
require_once('settings.php');

$options = array( 'hostname' => SOLR_SERVER_HOSTNAME );
$client = new SolrClient($options);

if (!$client->ping()) {
    exit ('Solr service not responding');
}  
