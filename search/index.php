<?php

/* @var $DB mysqli_native_moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */
?>
<?php

require_once('../config.php');
require_once('lib.php');
require_once('connection.php');
require_once('search.php');

require_login();
$PAGE->set_context(get_system_context());

@set_time_limit(0);

search_index($client);
search_optimize_index($client);

$mform = new search_form();
solr_display_search_form($mform);
//$q = required_param('queryfield', PARAM_TEXT);


if ($data = $mform->get_data()) {
	$q  = required_param('queryfield', PARAM_TEXT);
	$fq_title = optional_param('titlefilterqueryfield', '', PARAM_TEXT);
	$fq_author = optional_param('authorfilterqueryfield', '', PARAM_TEXT);
	$fq_module = optional_param('modulefilterqueryfield', '', PARAM_TEXT);
	
	$data->q  = $q;
	$data->fq_title  = $fq_title;
	$data->fq_author = $fq_author;
	$data->fq_module = $fq_module;
	
	solr_search_execute_query($client, $data);
}
