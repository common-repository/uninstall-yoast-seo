<?php
/*
Plugin Name: Uninstall Yoast SEO
Plugin URI: http://dirtcheapebooks.com
Description: Simply activate this plugin and it will deactivate Yoast SEO, clean up the WordPress database and then deactivate itself.
Version: 1.0
Author: Ralph van Troost
Author URI: http://dirtcheapebooks.com
*/

// Block direct access to script
if (!defined('ABSPATH')) {
	exit;
}

// Load file required for proper plugin deactivation processing
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Deactivate Yoast SEO plugin if its still activated
if (is_plugin_active('wordpress-seo/wp-seo.php')) {
	deactivate_plugins('wordpress-seo/wp-seo.php');
}

// Delete all database entries matching 'wpseo' and 'yst_sm_' from options and usermeta WP tables

// Load globals and set table names of WP database tables that need to be cleaned up
global $wpdb;
$options_table_name = $wpdb->prefix . "options";
$usermeta_table_name = $wpdb->prefix . "usermeta";

//// Delete entries
$wpdb->query("DELETE FROM $options_table_name WHERE option_name like '%wpseo%' OR option_name like '%yst_sm_%'");
$wpdb->query("DELETE FROM $usermeta_table_name WHERE meta_key like '%wpseo%'");

// Reset auto increment values

// As Yoast SEO flushes rewrite rules after deactivation first delete rewrite_rules from options table to properly reset its auto increment value
delete_option('rewrite_rules');// No worries, WP will simply regenerate the rewrite_rules option on the next front end page request
$wpdb->query("ALTER TABLE $options_table_name AUTO_INCREMENT = 1;");
$wpdb->query("ALTER TABLE $usermeta_table_name AUTO_INCREMENT = 1;");

// Remove the wpseo_onpage_fetch cronjob

// Get the timestamp for the next event
$timestamp = wp_next_scheduled('wpseo_onpage_fetch');

// Unschedule the cronjob to get the new indexibility status
wp_unschedule_event($timestamp, 'wpseo_onpage_fetch');

// Notify admin of Yoast SEO uninstall and plugin deactivation
function my_plugin_admin_notice() {
	echo '<div class="updated"><p><strong>Yoast SEO was successfully uninstalled</strong> and the <strong>Uninstall Yoast SEO plugin has been deactivated</strong>. Please feel free to <strong>delete</strong> the Yoast SEO & Uninstall Yoast SEO plugin files from your server.</p></div>';
	if (isset($_GET['activate'])) {
		unset($_GET['activate']);
	}
}
add_action('admin_notices', 'my_plugin_admin_notice');

// Deactivate Uninstall Yoast SEO plugin
if (is_plugin_active(plugin_basename(__FILE__))) {
	deactivate_plugins(plugin_basename(__FILE__));
}

?>