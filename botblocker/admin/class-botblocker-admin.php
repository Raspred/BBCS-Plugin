<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://globus.studio
 * @since      1.0.0
 *
 * @package    Botblocker
 * @subpackage Botblocker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Botblocker
 * @subpackage Botblocker/admin
 * @author     GLOBUS.studio <sales@globus.studio>
 */
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

if (defined('BOTBLOCKER_WIDGETS') && BOTBLOCKER_WIDGETS) {
    include('partials/botblocker-admin-dashboard-widgets.php');
}

class Botblocker_Admin
{

    private $BBCSA;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->BBCSA = [];
        $this->BBCSA['empty'] = '-';
    }
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        global $pagenow;
        $screen = get_current_screen();

        if ($pagenow === 'admin.php' && in_array($screen->id, [
            'toplevel_page_bbcs_dashboard',
            'botblocker_page_bbcs_settings',
            'botblocker_page_bbcs_integrations',
            'botblocker_page_bbcs_rules',
            'botblocker_page_bbcs_tools',
            'botblocker_page_bbcs_reports',
            'botblocker_page_bbcs_maintenance',
            'botblocker_page_bbcs_pro'
        ])) {
            wp_enqueue_style('google-fonts-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700&display=swap', [], null);

            wp_enqueue_style($this->plugin_name . '-bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap/css/bootstrap.min.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-theme', plugin_dir_url(__FILE__) . 'css/theme.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-default', plugin_dir_url(__FILE__) . 'css/default.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-fa', plugin_dir_url(__FILE__) . 'css/fa/css/all.min.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-flagicon', plugin_dir_url(__FILE__) . 'css/flagicon/flag-icon.min.css', array(), $this->version, 'all');
            //wp_enqueue_style($this->plugin_name . '-datatables', BOTBLOCKER_URL.'vendor/select2/css/select2.css', array(), $this->version, 'all');
            //wp_enqueue_style($this->plugin_name . '-datatables', BOTBLOCKER_URL.'vendor/select2-bootstrap-theme/select2-bootstrap.min.css', array(), $this->version, 'all');
            //wp_enqueue_style($this->plugin_name . '-datatables', 'https://cdn.datatables.net/2.1.3/css/dataTables.bootstrap5.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-datatables', 'https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.1.4/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/fh-4.0.1/r-3.0.2/datatables.min.css', array(), $this->version, 'all');
            
            //wp_enqueue_style($this->plugin_name . '-datatables', BOTBLOCKER_URL.'vendor/datatables/media/css/dataTables.bootstrap5.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'css/botblocker-admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
{
    global $pagenow;
    $screen = get_current_screen();

    if ($pagenow === 'admin.php' && in_array($screen->id, [
        'toplevel_page_bbcs_dashboard', 
        'botblocker_page_bbcs_settings',
        'botblocker_page_bbcs_integrations', 
        'botblocker_page_bbcs_reports', 
        'botblocker_page_bbcs_rules', 
        'botblocker_page_bbcs_tools',
        'botblocker_page_bbcs_maintenance',
        'botblocker_page_bbcs_pro'
    ])) {
        wp_enqueue_script($this->plugin_name . '-modernizr', plugin_dir_url(__FILE__) . 'js/modernizr.js', array('jquery'), $this->version, false);
        //wp_enqueue_script($this->plugin_name . '-popper', plugin_dir_url(__FILE__) . 'js/popper.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . '-bootstrap-js', plugin_dir_url(__FILE__) . 'js/bootstrap/js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
        //wp_enqueue_script($this->plugin_name . '-theme', plugin_dir_url(__FILE__) . 'js/theme.js', array('jquery'), $this->version, false);
        //wp_enqueue_script($this->plugin_name . '-theme-init', plugin_dir_url(__FILE__) . 'js/theme.init.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name .'-google-charts', 'https://www.gstatic.com/charts/loader.js', array(), null, false);

        wp_enqueue_script($this->plugin_name .'-datatables-pdf', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name .'-datatables-fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js', array('jquery'), $this->version, false);

       // wp_enqueue_script($this->plugin_name .'-datatables-js', 'https://cdn.datatables.net/2.1.3/js/dataTables.js', array('jquery'), $this->version, false);
       // wp_enqueue_script($this->plugin_name .'-datatables-bootstrap-js', 'https://cdn.datatables.net/2.1.3/js/dataTables.bootstrap5.min.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name .'-datatables-js', 'https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.1.4/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/fh-4.0.1/r-3.0.2/datatables.min.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name .'-raphael', 'https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.4/raphael-min.js', array(), null, false);
        wp_enqueue_script($this->plugin_name .'-justgage', 'https://cdnjs.cloudflare.com/ajax/libs/justgage/1.2.9/justgage.min.js', array($this->plugin_name .'-raphael'), null, false);

        wp_enqueue_script($this->plugin_name . '-common-js', plugin_dir_url(__FILE__) . 'js/bbcs-js/bbcs-common.js', array('jquery'), $this->version, true);
        wp_localize_script($this->plugin_name . '-common-js', 'botblockerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce("botblocker_nonce")
        ));        

        if ($screen->id === 'botblocker_page_bbcs_reports') {
            wp_enqueue_script($this->plugin_name . '-hits-js', plugin_dir_url(__FILE__) . 'js/bbcs-js/bbcs-hits.js', array('jquery'), $this->version, true);
            wp_localize_script($this->plugin_name . '-hits-js', 'botblockerData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("botblocker_nonce")
            ));            
        }

        if ($screen->id === 'botblocker_page_bbcs_rules') {
            wp_enqueue_script($this->plugin_name . '-rules-js', plugin_dir_url(__FILE__) . 'js/bbcs-js/bbcs-rules.js', array('jquery'), $this->version, true);
            wp_enqueue_script($this->plugin_name . '-rules-ipv4-js', plugin_dir_url(__FILE__) . 'js/bbcs-js/bbcs-rules-ipv4.js', array('jquery'), $this->version, true);
            wp_enqueue_script($this->plugin_name . '-rules-ipv6-js', plugin_dir_url(__FILE__) . 'js/bbcs-js/bbcs-rules-ipv6.js', array('jquery'), $this->version, true);
            wp_enqueue_script($this->plugin_name . '-rules-white-js', plugin_dir_url(__FILE__) . 'js/bbcs-js/bbcs-white.js', array('jquery'), $this->version, true);
            wp_enqueue_script($this->plugin_name . '-rules-path-js', plugin_dir_url(__FILE__) . 'js/bbcs-js/bbcs-path.js', array('jquery'), $this->version, true);

            wp_localize_script($this->plugin_name . '-rules-js', 'botblockerData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("botblocker_nonce")
            ));
            wp_localize_script($this->plugin_name . '-rules-ipv4-js', 'botblockerData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("botblocker_nonce")
            ));
            wp_localize_script($this->plugin_name . '-rules-ipv6-js', 'botblockerData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("botblocker_nonce")
            ));  
            wp_localize_script($this->plugin_name . '-rules-white-js', 'botblockerData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("botblocker_nonce")
            ));
            wp_localize_script($this->plugin_name . '-rules-path-js', 'botblockerData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("botblocker_nonce")
            ));          
        }
    }
}

public function add_admin_menu()
    {
        add_menu_page(
            'BotBlocker',
            'BotBlocker',
            'manage_options',
            'bbcs_dashboard',
            array($this, 'dashboard_page'),
            'dashicons-shield-alt',
            6
        );

        add_submenu_page(
            'bbcs_dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'bbcs_dashboard',
            array($this, 'dashboard_page')
        );

        add_submenu_page(
            'bbcs_dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'bbcs_settings',
            array($this, 'settings_page')
        );

        add_submenu_page(
            'bbcs_dashboard',
            'Integrations',
            'Integrations',
            'manage_options',
            'bbcs_integrations',
            array($this, 'integrations_page')
        );

        add_submenu_page(
            'bbcs_dashboard',
            'Reports',
            'Reports',
            'manage_options',
            'bbcs_reports',
            array($this, 'reports_page')
        );

        add_submenu_page(
            'bbcs_dashboard',
            'Rules',
            'Rules',
            'manage_options',
            'bbcs_rules',
            array($this, 'rules_page')
        );

        add_submenu_page(
            'bbcs_dashboard',
            'Tools',
            'Tools',
            'manage_options',
            'bbcs_tools',
            array($this, 'tools_page')
        );

        add_submenu_page(
            'bbcs_dashboard',
            'Maintenance',
            'Maintenance',
            'manage_options',
            'bbcs_maintenance',
            array($this, 'maintenance_page')
        );

        add_submenu_page(
            'bbcs_dashboard',
            'PRO',
            'PRO',
            'manage_options',
            'bbcs_pro',
            array($this, 'pro_page')
        );        
    }

    public function dashboard_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-dashboard.php';
    }

    public function settings_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-settings.php';
    }

    public function reports_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-reports.php';
    }

    public function rules_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-rules.php';
    }

    public function tools_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-tools.php';
    }

    public function integrations_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-integrations.php';
    }

    public function maintenance_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-maintenance.php';
    }

    public function pro_page()
    {
        require plugin_dir_path(__FILE__) . 'partials/botblocker-admin-display-pro.php';
    }   

    public function loadDirs()
    {
        global $BBCSA;
        $this->BBCSA['botblockerUrl'] = BOTBLOCKER_URL;
        $this->BBCSA['version'] = BOTBLOCKER_VERSION;

        $this->BBCSA['dirs']['public'] =  BOTBLOCKER_DIR . 'public/';
        $this->BBCSA['dirs']['languages'] =  BOTBLOCKER_DIR . 'languages/';
        $this->BBCSA['dirs']['includes'] =  BOTBLOCKER_DIR . 'includes/';
        $this->BBCSA['dirs']['admin'] =  BOTBLOCKER_DIR . 'admin/';
        $this->BBCSA['dirs']['data'] =  BOTBLOCKER_DIR . 'data/';
        $this->BBCSA['dirs']['vendor'] =  BOTBLOCKER_DIR . 'vendor/';
        $BBCSA = $this->BBCSA;
    }

    public function run()
    {        
        global $BBCSA;
        
        $this->loadDirs();
        $this->BBCSA['custom_avatar'] = $this->BBCSA['botblockerUrl'] . 'admin/img/avatar.png';
        $this->BBCSA['botblocker_logo'] = $this->BBCSA['botblockerUrl'] . 'admin/img/logo.png';
        $this->BBCSA['botblocker_small'] = $this->BBCSA['botblockerUrl'] . 'admin/img/logo_s.png';

        $this->BBCSA['pages'] = array(
            'dashboard' => admin_url('admin.php?page=bbcs_dashboard'),
            'settings' => admin_url('admin.php?page=bbcs_settings'),
            'integrations' => admin_url('admin.php?page=bbcs_integrations'),
            'reports' => admin_url('admin.php?page=bbcs_reports'),
            'rules' => admin_url('admin.php?page=bbcs_rules'),
            'tools' => admin_url('admin.php?page=bbcs_tools'),
            'maintenance' => admin_url('admin.php?page=bbcs_maintenance'),
            'pro' => admin_url('admin.php?page=bbcs_pro')
        );

        $this->BBCSA['files'] = array(
            'IPv4' => $this->BBCSA['botblockerUrl'] . 'data/BotBlocker-test-IPv4-list.txt',
            'IPv6' => $this->BBCSA['botblockerUrl'] . 'data/BotBlocker-test-IPv6-list.txt'
        );

        $BBCSA = $this->BBCSA; // Sync global variable
    }

}
