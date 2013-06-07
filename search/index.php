<?php

/* @var $DB mysqli_native_moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */
?>
<?php

require_once('../config.php');
require_once('connection.php');
require_once('lib.php');
//To be removed later
require_once('tests/test_search.php');

require_login();
$PAGE->set_context(get_system_context());

@set_time_limit(0);

search_index();
search_optimize_index();

//@TODO: Proper search forms
test_solr_query();