<?php

require_once($CFG->libdir . '/formslib.php');
require_once('search_form.php');

require_login();

function solr_display_search_form($mform){
	$mform->display();
}

function solr_search_execute_query(SolrWrapper $client, $q){
	//solr_display_search_form();
	$query = new SolrQuery();
	$query->setQuery($q);
	$query->addField('id')->addField('title')->addField('content');
	$query_response = $client->query($query);
	$response = $query_response->getResponse();
	print_r($response);
}
