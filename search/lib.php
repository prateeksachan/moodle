<?php

/* @var $DB mysqli_native_moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */
?>
<?php

require_once($CFG->dirroot . '/lib/accesslib.php');
require_once('solr/lib.php');

define('SEARCH_INDEX_PATH', $CFG->dataroot . '/search');
define('SEARCH_TYPE_HTML', 1);
define('SEARCH_TYPE_TEXT', 2);
define('SEARCH_TYPE_FILE', 3);

define('SEARCH_ACCESS_DENIED', 0);
define('SEARCH_ACCESS_GRANTED', 1);
define('SEARCH_ACCESS_DELETED', 2);

/**
 * Modules activated for Global Search.
 * @return array $mods
 */
function search_get_modules(){
    global $DB;
    $mods = $DB->get_records('modules', null, 'name', 'id,name');
    foreach ($mods as $key => $mod) {
        if (!plugin_supports('mod', $mod->name, FEATURE_GLOBAL_SEARCH)) {
            unset($mods[$key]);
        }
    }
    return $mods;
}

/**
 * Search API functions for modules.
 * @return stdClass object $functions
 */
function search_get_iterators() {
    $mods = search_get_modules();
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
 * @param SolrWrapper $client
 */
function search_optimize_index(SolrWrapper $client) {
    $client->optimize();
}


/**
 * Index all documents.
 * @param SolrWrapper $client
 */
function search_index(SolrWrapper $client) {
    mtrace("Memory usage:" . memory_get_usage(), '<br/>');
    set_time_limit(576000);
    $iterators = search_get_iterators();
    mtrace("Memory usage:" . memory_get_usage(), '<br/>');
    foreach ($iterators as $name => $iterator) {
        mtrace('Processing module ' . $iterator->module, '<br />');
        $indexingstart = time();
        $iterfunction = $iterator->iterator;
        $getdocsfunction = $iterator->documents;
        $lastindexrun = get_config('search', $name . '_lastindexrun');
        $recordset = $iterfunction($lastindexrun);
        $numrecords = 0;
        $numdocs = 0;
        $numdocsignored = 0;
        foreach ($recordset as $record) {
            mtrace("$name,{$record->id}", '<br/>');
            mtrace("Memory usage:" . memory_get_usage(), '<br/>');
            ++$numrecords;
            echo 'norecords: '.$numrecords;
            $timestart = microtime(true);
            $documents = $getdocsfunction($record->id);
            
            foreach ($documents as $solrdocument) {
                switch (($solrdocument->getField('type')->values[0])) {
                    case SEARCH_TYPE_HTML:
                        $client->addDocument($solrdocument);
                        mtrace("Memory usage: (doc added)" . memory_get_usage(), '<br/>');
                        ++$numdocs;
                        break;
                    case SEARCH_TYPE_TEXT:
                        $client->addDocument($solrdocument);
                        mtrace("Memory usage: (doc added)" . memory_get_usage(), '<br/>');
                        ++$numdocs;
                        break;
                    case SEARCH_TYPE_FILE:
                        //TODO Ingtegrate Apache Tika here
                        ++$numdocs;
                        break;
                    default:
                        ++$numdocsignored;
                        throw new search_ex("Incorrect document format encountered");
                }
            }
            $timetaken = microtime(true) - $timestart;
            mtrace("Time $numrecords: $timetaken", '<br/>');
        }
        $recordset->close();
        if ($numrecords > 0) {
            $client->commit();
            $indexingend = time();
            set_config($name . '_indexingstart', $indexingstart, 'search');
            set_config($name . '_indexingend', $indexingend, 'search');
            set_config($name . '_lastindexrun', $record->modified, 'search');
            set_config($name . '_docsignored', $numdocsignored, 'search');
            set_config($name . '_docsprocessed', $numdocs, 'search');
            set_config($name . '_recordsprocessed', $numrecords, 'search');
            mtrace("Processed $numrecords records containing $numdocs documents for " . $iterator->module);
            echo 'commits completed'.'<br>';
        }
    }
}

/**
 * Resets config_plugin table after index deletion as re-indexing will be done from start.
 * optional @param string $s containing modules whose index was chosen to be deleted.
 */
function search_reset_config($s = NULL){
    if (!empty($s)){
        $mods = explode(',', $s);
    }
    else{
        $get_mods = search_get_modules();
        $mods = array();
        foreach ($get_mods as $mod){
            $mods[] = $mod->name;
        }
    }
    foreach ($mods as $key => $name) {
        set_config($name . '_indexingstart', 0, 'search');
        set_config($name . '_indexingend', 0, 'search');
        set_config($name . '_lastindexrun', 0, 'search');
        set_config($name . '_docsignored', 0, 'search');
        set_config($name . '_docsprocessed', 0, 'search');
        set_config($name . '_recordsprocessed', 0, 'search');
    }
}

/**
 * Deletes index.
 * @param SolrWrapper $client
 * @param stdClass object $data
 */
function search_delete_index(SolrWrapper $client, $data){
    if (!empty($data->module)){
        $client->deleteByQuery('module:' . $data->module);
        search_reset_config($data->module);
    }
    else{
        $client->deleteByQuery('*:*');
        search_reset_config();   
    }
    $client->commit();
}

/**
 * Returns Global Search configuration settings from config_plugin table.
 * @param array $mods
 * @return array $configsettings
 */
function search_get_config($mods) {
    $all = get_config('search');
    $configvars = array('indexingstart', 'indexingend', 'lastindexrun', 'docsignored', 'docsprocessed', 'recordsprocessed');

    $configsettings =  array();
    foreach ($mods as $mod) {
        $configsettings[$mod] = new stdClass();
        foreach ($configvars as $var) {
            $name = "{$mod}_$var";
            if (!empty($all->$name)) {
                $configsettings[$mod]->$var = $configsettings->$name;
            }
            else {
                $configsettings[$mod]->$var = 0;
            }
        }
        if (!empty($configsettings[$mod]->lastindexrun)) {
            $configsettings[$mod]->lastindexrun = userdate($configsettings[$mod]->lastindexrun);
        } else {
            $configsettings[$mod]->lastindexrun = "never";
        }
    }
    return $configsettings;
}
