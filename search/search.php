<?php

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/search/search_form.php');

require_login();

function solr_display_search_form($mform){
	$mform->display();
}

function solr_execute_query(SolrWrapper $client, $data){
	$query = new SolrQuery();
	$query->setQuery($data->queryfield);
	$query->setStart(SEARCH_SET_START);
	$query->setRows(SEARCH_SET_ROWS);
	solr_addFields($query);

	if (!empty($data->titlefilterqueryfield)){
		$query->addFilterQuery($data->titlefilterqueryfield);
	}
	if (!empty($data->authorfilterqueryfield)){
		$query->addFilterQuery($data->authorfilterqueryfield);
	}
	if (!empty($data->modulefilterqueryfield)){
		$query->addFilterQuery($data->modulefilterqueryfield);
	}
	
	solr_query_response($client, $client->query($query));
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

function solr_addFields($query){
	$fields = array('type', 'id', 'user', 'created', 'modified', 'author', 'name', 'title', 'intro',
					'content', 'courseid', 'mime', 'contextlink', 'directlink', 'filepath', 'module');

	foreach ($fields as $field){
		$query->addField($field);
	}
}

function solr_query_response(SolrWrapper $client, $query_response){
	$response = $query_response->getResponse();
	$totalnumfound = $response->response->numFound;
	$docs = $response->response->docs;
	$numgranted = 0;

	foreach ($docs as $key => $value){
		$solr_id = explode("_", $value->id);
		$access_func = $solr_id[0] . '_search_access';
		echo $access_func($solr_id[1]);
		switch ($access_func($solr_id[1])){
			case SEARCH_ACCESS_DELETED:
				search_delete_index_by_id($client, $value->id);
				unset($docs[$key]);
				break;
			case SEARCH_ACCESS_DENIED:
				unset($docs[$key]);
				break;
			case SEARCH_ACCESS_GRANTED:
				$numgranted++;
				break;
		}

		if ($numgranted == SEARCH_MAX_RESULTS){
			$docs = array_slice($docs, 0, SEARCH_MAX_RESULTS, true);
			break;
		}
	}
	print_r($docs);

}
