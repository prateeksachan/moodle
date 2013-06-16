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

//search_index($client);
//search_optimize_index($client);

$mform = new search_form();
solr_display_search_form($mform);

if ($data = $mform->get_data()) {
	solr_prepare_query($client, $data);
}
