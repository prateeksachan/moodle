<?php

// This file defines everything related to globalsearch

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page

    // "solr" settingpage

    $temp = new admin_settingpage('searchengine', new lang_string('searchengine', 'admin'));

    $solr_installed = false;
    // Add other search engine to be implemented (if) later.
    // ${anothersearchengine}_installed = false;

    $options = array('solr' => 'Apache Solr');
    $temp->add(new admin_setting_configselect('search_engine', new lang_string('choosesearchengine', 'admin'), new lang_string('choosesearchengine_desc', 'admin'), 'solr', $options));

    if (!empty($CFG->search_engine)) {
        switch ($CFG->search_engine) {
            case 'solr':
                if (function_exists('solr_get_version')) {
                    $version = solr_get_version();
                    $solr_installed = true;
                }
                break;
            /*
            case '{anothersearchengine}':
                if (check_for_another_search_engine) {
                    {anothersearchengine}_installed = true;
                }
                break;
            */
            default:
                break;
        }
    }

    $ADMIN->add('globalsearch', $temp);

    if ($solr_installed) {
        $temp = new admin_settingpage('solrsettingpage', new lang_string('solrsetting', 'admin'));

        $hostname = '127.0.0.1';
        $options = array('4.0'=>'4.x', '3.0'=>'3.x');
        $temp->add(new admin_setting_configselect('solr_version', new lang_string('solrversion', 'admin'), new lang_string('solrversion_desc', 'admin', $version), (substr($version, 0, 2) == '2.' ? '4.0' : '3.0'), $options));
        $temp->add(new admin_setting_configtext('solr_server_hostname', new lang_string('solrserverhostname', 'admin'), new lang_string('solrserverhostname_desc', 'admin'), $hostname, PARAM_TEXT));
        $temp->add(new admin_setting_configcheckbox('solr_secure', new lang_string('solrsecuremode', 'admin'), new lang_string('solrsecuremode_desc', 'admin'), 0, 1, 0));
        $temp->add(new admin_setting_configtext('solr_server_port', new lang_string('solrhttpconnectionport', 'admin'), new lang_string('solrhttpconnectionport_desc', 'admin'), (!empty($CFG->solr_secure) ? 8443 : 8983), PARAM_INT));
        $temp->add(new admin_setting_configtext('solr_server_username', new lang_string('solrauthuser', 'admin'), new lang_string('solrauthuser_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('solr_server_password', new lang_string('solrauthpassword', 'admin'), new lang_string('solrauthpassword_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('solr_server_timeout', new lang_string('solrhttpconnectiontimeout', 'admin'), new lang_string('solrhttpconnectiontimeout_desc', 'admin'), 30, PARAM_INT));
        $temp->add(new admin_setting_configtext('solr_ssl_cert', new lang_string('solrsslcert', 'admin'), new lang_string('solrsslcert_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('solr_ssl_cert_only', new lang_string('solrsslcertonly', 'admin'), new lang_string('solrsslcertonly_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('solr_ssl_key', new lang_string('solrsslkey', 'admin'), new lang_string('solrsslkey_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('solr_ssl_keypassword', new lang_string('solrsslkeypassword', 'admin'), new lang_string('solrsslkeypassword_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('solr_ssl_cainfo', new lang_string('solrsslcainfo', 'admin'), new lang_string('solrsslcainfo_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('solr_ssl_capath', new lang_string('solrsslcapath', 'admin'), new lang_string('solrsslcapath_desc', 'admin'), '', PARAM_RAW));

        $ADMIN->add('globalsearch', $temp);
    }

    if (!empty($CFG->enableglobalsearch)) {
        $temp = new admin_settingpage('activatemods', new lang_string('activatemods', 'admin'));

        $supported_mods = array('course', 'book', 'forum', 'glossary', 'label', 'lesson', 'page', 'resource', 'url', 'wiki'); // add a module here to make it gs_supported
        foreach ($supported_mods as $mod) {
            $temp->add(new admin_setting_configcheckbox('gs_support_' . $mod, new lang_string('gs_support_mod', 'admin', ucfirst($mod)), new lang_string('gs_support_mod_desc', 'admin', ucfirst($mod)), 1, 1, 0));
        }

        $ADMIN->add('globalsearch', $temp);
    }

    $ADMIN->add('globalsearch', new admin_externalpage('statistics', new lang_string('statistics', 'admin'), "$CFG->wwwroot/search/admin.php"));
}
