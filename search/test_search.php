<?php
function test_solr_query(){
	$query = new SolrQuery();
	$query->setQuery('Geography');

	//Fields to be returned in the result 
	$query->addField('id')->addField('title')->addField('contextlink')->addField('modified');

	//Sends the query to the server (server that is defined above)
	$query_response = $client->query($query);

	//Returns the SolrObjec in the form XML that was returned from the server after firing the query
	$response = $query_response->getResponse();

	//Printing the XML response returned from the server
	print_r($response);
}