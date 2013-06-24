<?php

require_once('../config.php');
require_once($CFG->dirroot . '/search/connection.php');
require_once($CFG->dirroot . '/search/lib.php');
require_once($CFG->dirroot . '/search/search.php');

require_login();
$PAGE->set_context(get_system_context());

@set_time_limit(0);

$mform = new search_form();
solr_display_search_form($mform);

if ($data = $mform->get_data()) {
	solr_prepare_query($client, $data);
}
