<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/arielhr1987
 * @since             1.0.0
 * @package           Leira_Roles
 *
 * @wordpress-plugin
 * Plugin Name:     Roles & Capabilities
 * Plugin URI:      https://github.com/arielhr1987/leira-roles
 * Description:     Roles & Capabilities is a plugin that will allow you to manage user roles and capabilities and also assign capabilities to specific users.
 * Version:         1.1.10
 * Author:          Ariel
 * Author URI:      https://leira.dev
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     leira-roles
 * Domain Path:     /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LEIRA_ROLES_VERSION', '1.1.10' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-leira-roles-activator.php
 */
function activate_leira_roles() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-leira-roles-activator.php';
	Leira_Roles_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-leira-roles-deactivator.php
 */
function deactivate_leira_roles() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-leira-roles-deactivator.php';
	Leira_Roles_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_leira_roles' );
register_deactivation_hook( __FILE__, 'deactivate_leira_roles' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-leira-roles.php';

/**
 * Helper method to get the main instance of the plugin
 *
 * @return Leira_Roles
 * @since    1.1.0
 * @access   global
 */
function leira_roles() {
	return Leira_Roles::instance();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.0
 */
leira_roles()->run();
