<?php

require_once('../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/search/lib.php');

require_login();

class search_form extends moodleform {

    function definition() {

		$mform =& $this->_form;
		$mform->addElement('header', 'search', get_string('search', 'search'));

		$mform->addElement('text', 'queryfield', get_string('query', 'search'));
		$mform->setType('queryfield', PARAM_TEXT);
		$mform->addRule('queryfield', get_string('emptyqueryfield', 'search'), 'required', null, 'client');

		$this->add_action_buttons($cancel = false, $submitlabel='Search');
		$mform->setDefault('action', '');

	}

}
$PAGE->set_context(get_system_context());

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