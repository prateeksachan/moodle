<?php

require_once($CFG->libdir . '/formslib.php');
require_once('search_form.php');

require_login();

function solr_display_search_form($mform){
	$mform->display();
}

function solr_search_execute_query(SolrWrapper $client, $data){
	//solr_display_search_form();
	$query = new SolrQuery();
	$query->setQuery($data->q);
	$query->addField('id')->addField('title')->addField('content');
	$data = solr_check_filter_query($data);
	if (!empty($data->fq_title)){
		$query->addFilterQuery('title:' . $data->fq_title);
	}
	if (!empty($data->fq_author)){
		$query->addFilterQuery('author:' . $data->fq_author);
	}
	if (!empty($data->fq_module)){
		$query->addFilterQuery('module:' . $data->fq_module);
	}
	$query_response = $client->query($query);
	$response = $query_response->getResponse();
	print_r($response);
}
