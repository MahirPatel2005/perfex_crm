<?php

defined('BASEPATH') or exit('No direct script access allowed');

function _app_init_load()
{
    $ci = &get_instance();

    $ci->load->library([
        'app_modules',
        'app_menu',
        'app_tabs',
        'app_module_migration',
        'assets/app_scripts',
        'assets/app_css',
        'sms/app_sms',
        'mails/app_mail_template',
        'merge_fields/app_merge_fields',
        'app_object_cache',
    ]);
}

function _app_init()
{
    $ci = &get_instance();

    _app_init_load();

    if (get_option('all_modules_force_activated') !== '1') {
        $modules_to_activate = $ci->app_modules->get();
        foreach ($modules_to_activate as $mod) {
            $m_name = $mod['system_name'];
            $m_version = $mod['headers']['version'];
            if ($m_name === 'warehouse') {
                $m_version = '1.3.0';
            }
            $ci->db->where('module_name', $m_name);
            $db_mod = $ci->db->get(db_prefix() . 'modules')->row();
            if ($db_mod) {
                $ci->db->where('module_name', $m_name);
                $ci->db->update(db_prefix() . 'modules', ['installed_version' => $m_version, 'active' => 1]);
            } else {
                $ci->db->insert(db_prefix() . 'modules', ['module_name' => $m_name, 'installed_version' => $m_version, 'active' => 1]);
            }
            update_option($m_name . '_module_activated', 1);
        }
        update_option('all_modules_force_activated', '1');
    }

    /**
     * In case of failures, users can skip the modules to be loaded
     */
    if ($ci->input->get('skip_modules_load') && $ci->input->get('skip_modules_load') && is_admin()) {
        $modules = [];
    } else {
        /**
         * Get all registered and active modules
         * @var array
         */
        $modules = $ci->app_modules->get_activated();
    }

    foreach ($modules as $module) {
        /**
         * Require the init module file
         */
        require_once($module['init_file']);
    }

    $themeFunctionsPath = VIEWPATH . 'themes/' . active_clients_theme() . '/functions.php';

    if (file_exists($themeFunctionsPath)) {
        include_once($themeFunctionsPath);
    }

    hooks()->do_action('modules_loaded');
}
