<?php

require_once($CFG->libdir . '/formslib.php');
require_once('search_form.php');

require_login();

function solr_display_search_form($mform){
	$mform->display();
}

function solr_execute_query(SolrWrapper $client, $data){
	//solr_display_search_form();
	$query = new SolrQuery();
	$query->setQuery($data->queryfield);
	$query->addField('id')->addField('title')->addField('content');
	if (!empty($data->titlefilterqueryfield)){
		$query->addFilterQuery($data->titlefilterqueryfield);
	}
	if (!empty($data->authorfilterqueryfield)){
		$query->addFilterQuery($data->authorfilterqueryfield);
	}
	if (!empty($data->modulefilterqueryfield)){
		$query->addFilterQuery($data->modulefilterqueryfield);
	}
	$query_response = $client->query($query);
	$response = $query_response->getResponse();
	print_r($response);
}

function solr_prepare_query(SolrWrapper $client, $data){
	if (!empty($data->titlefilterqueryfield)){
		$data->titlefilterqueryfield = 'title:' . $data->titlefilterqueryfield;
	}
	if (!empty($data->authorfilterqueryfield)){
		$data->authorfilterqueryfield = 'author:' . $data->authorfilterqueryfield;
	}
	if (!empty($data->modulefilterqueryfield)){
		$data->modulefilterqueryfield = 'module:' . $data->modulefilterqueryfield;
	}
	solr_execute_query($client, $data);
}
