<?php

/* @var $DB mysqli_native_moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */
?>
<?php

require_once($CFG->dirroot . '/lib/accesslib.php');


define('SEARCH_INDEX_PATH', $CFG->dataroot . '/search');
define('SEARCH_TYPE_TEXT', 0);
define('SEARCH_TYPE_FILE', 1);
define('SEARCH_TYPE_HTML', 2);

define('SEARCH_ACCESS_DENIED', 0);
define('SEARCH_ACCESS_GRANTED', 1);
define('SEARCH_ACCESS_DELETED', 2);

function search_get_iterators() {
    global $DB;
    $mods = $DB->get_records('modules', null, 'name', 'id,name');
    foreach ($mods as $k => $mod) {
        if (!plugin_supports('mod', $mod->name, FEATURE_GLOBAL_SEARCH)) {
            unset($mods[$k]);
        }
    }
    $functions = array();
    foreach ($mods as $mod) {
        if (!function_exists($mod->name . '_search_iterator')) {
            throw new coding_exception('Module declared FEATURE_GLOBAL_SEARCH but function \'' . $mod->name . '_search_iterator' . '\' is missing.');
        }
        if (!function_exists($mod->name . '_search_get_documents')) {
            throw new coding_exception('Module declared FEATURE_GLOBAL_SEARCH but function \'' . $mod->name . '_search_get_documents' . '\' is missing.');
        }
        if (!function_exists($mod->name . '_search_access')) {
            throw new coding_exception('Module declared FEATURE_GLOBAL_SEARCH but function \'' . $mod->name . '_search_access' . '\' is missing.');
        }
        /*
         * Store the respective core search functions for each module to be called later.
         * when committing the documents.
        */ 
        $functions[$mod->name] = new stdClass();
        $functions[$mod->name]->iterator = $mod->name . '_search_iterator';
        $functions[$mod->name]->documents = $mod->name . '_search_get_documents';
        $functions[$mod->name]->access = $mod->name . '_search_access';
        $functions[$mod->name]->module = $mod->name;
    }

    return $functions;
}


/**
 * Merge separate index segments into one.
 * solr function:: optimize() does this.
 * To be done later
 */
function search_optimize_index() {
    $client->optimize();
}


/**
 * Index all documents.
 */
function search_index() {
    mtrace("Memory usage:" . memory_get_usage(), '<br/>');
    set_time_limit(576000);
    
    $iterators = search_get_iterators();
    mtrace("Memory usage:" . memory_get_usage(), '<br/>');
    foreach ($iterators as $name => $iterator) {
        mtrace('Processing module ' . $iterator->module, '<br />');
        $indexingstart = time();
        $iterfunction = $iterator->iterator;
        $getdocsfunction = $iterator->documents;
        //@TODO: get the timestamp of the last commited run and put here
        $lastrun = 0;
        $recordset = $iterfunction($lastrun);
        $norecords = 0;
        $nodocuments = 0;
        $nodocumentsignored = 0;
        foreach ($recordset as $record) {
            mtrace("$name,{$record->id}", '<br/>');
            mtrace("Memory usage:" . memory_get_usage(), '<br/>');
            ++$norecords;
            echo 'norecords: '.$norecords;
            $timestart = microtime(true);
            $documents = $getdocsfunction($record->id);
            
            foreach ($documents as $solrdocument) {
                if ($solrdocument) {
                    $client->addDocument($solrdocument);
                    mtrace("Memory usage: (doc added)" . memory_get_usage(), '<br/>');
                    ++$nodocuments;
                } else {
                    ++$nodocumentsignored;
                }
            }
            $timetaken = microtime(true) - $timestart;
            mtrace("Time $norecords: $timetaken", '<br/>');
        }
        $recordset->close();
        if ($norecords > 0) {
            $client->commit();
            echo 'commits completed'.'<br>';
        }
    }
}