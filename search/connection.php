<?php

/**
 * Connecting the Solr server as defined by parameters in bootstrap.php
 * Throws an error if the solr server is not running
**/
require_once($CFG->dirroot . '/search/settings.php');
require_once($CFG->dirroot . '/search/solr/lib.php');

$options = array( 'hostname' => SOLR_SERVER_HOSTNAME );
$object = new SolrClient($options, SOLR_VERSION);
$client = new SolrWrapper($object);

solr_check_server($client);
