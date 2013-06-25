<?php

require_once('../config.php');
require_once($CFG->dirroot . '/search/connection.php');
require_once($CFG->dirroot . '/search/lib.php');
require_once($CFG->dirroot . '/search/search.php');

require_login();

@set_time_limit(0);

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('standard');
$PAGE->set_url($FULLME);
$PAGE->set_title(get_string('globalsearch', 'search'));
$PAGE->set_heading(get_string('globalsearch', 'search'));

$search = trim(optional_param('search', '', PARAM_NOTAGS));
$fq_module = trim(optional_param('fq_module', '', PARAM_NOTAGS));

$mform = new search_form();

echo $OUTPUT->header();
solr_display_search_form($mform);

if (!empty($search)) { 
	$data = new stdClass();
	$data->queryfield = $search;
	$data->modulefilterqueryfield = $fq_module;
	solr_prepare_query($client, $data);
}

if ($data = $mform->get_data()) {
	solr_prepare_query($client, $data);
}
echo $OUTPUT->footer();
