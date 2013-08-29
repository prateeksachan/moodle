<?php

// This file defines everything related to globalsearch

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page

    // "solr" settingpage

    $temp = new admin_settingpage('searchengine', new lang_string('searchengine', 'admin'));
    $is_solr_installed = false;
    // Insert variable here for other search engine.

    // Add other search engine to be implemented (if) later.
    $options = array('solr' => 'Apache Solr');
    $temp->add(new admin_setting_configselect('SEARCH_ENGINE', new lang_string('choosesearchengine', 'admin'), new lang_string('choosesearchengine_desc', 'admin'), 'solr', $options));

    switch ($CFG->SEARCH_ENGINE) {
        case 'solr':
            if (function_exists('solr_get_version')) {
                $is_solr_installed = true;
                $version = solr_get_version();
                $options = array('4.0'=>'4.x', '3.0'=>'3.x');
                if ($version != '1.0.3-alpha') {
                    array_shift($options);
                }
                $temp->add(new admin_setting_configselect('SOLR_VERSION', new lang_string('solrversion', 'admin'), new lang_string('solrversion_desc', 'admin', $version), ($version == '1.0.3-alpha' ? '4.0' : '3.0'), $options));
            }
            break;
        default:
            break;
    }

    $ADMIN->add('globalsearch', $temp);

    if ($CFG->SEARCH_ENGINE == 'solr' and $is_solr_installed) {
        $temp = new admin_settingpage('solrsettingpage', new lang_string('solrsetting', 'admin'));

        $hostname = '127.0.0.1';
        $temp->add(new admin_setting_configtext('SOLR_SERVER_HOSTNAME', new lang_string('solrserverhostname', 'admin'), new lang_string('solrserverhostname_desc', 'admin'), $hostname, PARAM_TEXT));
        $temp->add(new admin_setting_configcheckbox('SOLR_SECURE', new lang_string('solrsecuremode', 'admin'), new lang_string('solrsecuremode_desc', 'admin'), 0, 1, 0));
        $temp->add(new admin_setting_configtext('SOLR_SERVER_PORT', new lang_string('solrhttpconnectionport', 'admin'), new lang_string('solrhttpconnectionport_desc', 'admin'), (($CFG->SOLR_SECURE) ? 8443 : 8983), PARAM_INT));
        $temp->add(new admin_setting_configtext('SOLR_SERVER_USERNAME', new lang_string('solrauthuser', 'admin'), new lang_string('solrauthuser_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('SOLR_SERVER_PASSWORD', new lang_string('solrauthpassword', 'admin'), new lang_string('solrauthpassword_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('SOLR_SERVER_TIMEOUT', new lang_string('solrhttpconnectiontimeout', 'admin'), new lang_string('solrhttpconnectiontimeout_desc', 'admin'), 30, PARAM_INT));
        $temp->add(new admin_setting_configtext('SOLR_SSL_CERT', new lang_string('solrsslcert', 'admin'), new lang_string('solrsslcert_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('SOLR_SSL_CERT_ONLY', new lang_string('solrsslcertonly', 'admin'), new lang_string('solrsslcertonly_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('SOLR_SSL_KEY', new lang_string('solrsslkey', 'admin'), new lang_string('solrsslkey_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('SOLR_SSL_KEYPASSWORD', new lang_string('solrsslkeypassword', 'admin'), new lang_string('solrsslkeypassword_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('SOLR_SSL_CAINFO', new lang_string('solrsslcainfo', 'admin'), new lang_string('solrsslcainfo_desc', 'admin'), '', PARAM_RAW));
        $temp->add(new admin_setting_configtext('SOLR_SSL_CAPATH', new lang_string('solrsslcapath', 'admin'), new lang_string('solrsslcapath_desc', 'admin'), '', PARAM_RAW));

        $ADMIN->add('globalsearch', $temp);
    }

    if ($is_solr_installed) { // Use OR with if other search engine implemented.
        $temp = new admin_settingpage('activatemods', new lang_string('activatemods', 'admin'));

        $supported_mods = array('book', 'forum', 'glossary', 'label', 'lesson', 'page', 'resource', 'url', 'wiki'); // add a module here to make it gs_supported
        foreach ($supported_mods as $mod) {
            $temp->add(new admin_setting_configcheckbox('gs_support_' . $mod, new lang_string('gs_support_mod', 'admin', ucfirst($mod)), new lang_string('gs_support_mod_desc', 'admin', ucfirst($mod)), 1, 1, 0));
        }

        $ADMIN->add('globalsearch', $temp);
    }

    if ($is_solr_installed) { // Use OR with if other search engine implemented.
        $ADMIN->add('globalsearch', new admin_externalpage('statistics', new lang_string('statistics', 'admin'), "$CFG->wwwroot/search/admin.php"));
    }
}
